<?php

namespace App\Console\Commands;

use App\Mail\MagicLoginLink;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first();

        if (!$user) {
            $this->error('No users found in the database. Please create a user first.');
            return 1;
        }

        $mailer = Config::get('mail.default');
        $mailDriver = Config::get("mail.mailers.{$mailer}.transport", $mailer);

        $this->info('Sending test email to: ' . $user->email);
        $this->info('Using mail driver: ' . $mailDriver);

        // Additional Brevo-specific info
        if ($mailDriver === 'brevo') {
            $this->info('Using Brevo API for sending email');
        } elseif ($mailDriver === 'smtp' && Config::get('mail.mailers.smtp.host') === 'smtp-relay.brevo.com') {
            $this->info('Using Brevo SMTP for sending email');
        }

        try {
            Mail::to($user)->send(new MagicLoginLink(
                $user,
                'test-token-123456',
                Carbon::now()->addHour()->format('Y-m-d H:i:s')
            ));

            $this->info('Email sent successfully!');
            
            if ($mailDriver === 'smtp' && Config::get('mail.mailers.smtp.host') === 'mailhog') {
                $this->info('Since mail is configured to use MailHog, check the web interface at: http://localhost:8025');
            } elseif ($mailDriver === 'log') {
                $this->info('Since mail is configured to use the "log" driver, check your log file at: storage/logs/laravel.log');
            } elseif ($mailDriver === 'brevo' || 
                     ($mailDriver === 'smtp' && Config::get('mail.mailers.smtp.host') === 'smtp-relay.brevo.com')) {
                $this->info('Since mail is configured to use Brevo, the email should be delivered to the recipient\'s inbox soon.');
                $this->info('You can also check your Brevo dashboard for delivery status.');
            } else {
                $this->info('The email should be delivered to the recipient\'s inbox soon.');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            
            // Provide more helpful error messages for Brevo-specific issues
            if (stripos($e->getMessage(), 'brevo') !== false || 
                stripos($e->getMessage(), 'sendinblue') !== false) {
                $this->warn('This appears to be a Brevo-specific error:');
                $this->warn('1. Check that your Brevo API key or SMTP credentials are correct');
                $this->warn('2. Verify your Brevo account is active and has sending credits');
                $this->warn('3. Check if you\'ve reached your daily sending limit');
            }
            
            return 1;
        }
    }
} 