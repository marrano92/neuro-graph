<?php
// [ai-generated-code]
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\MailManager;
use GuzzleHttp\Client;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoSmtpTransport;

class BrevoMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Brevo API transport
        Mail::extend('brevo', function ($config) {
            $apiKey = $config['api_key'] ?? env('BREVO_API_KEY');
            
            if (!$apiKey) {
                throw new \Exception('Brevo API key is not set. Please provide BREVO_API_KEY in your .env file.');
            }
            
            return new BrevoApiTransport($apiKey);
        });

        // You could also register the SMTP transport if needed
        Mail::extend('brevo-smtp', function ($config) {
            $username = $config['username'] ?? env('MAIL_USERNAME');
            $password = $config['password'] ?? env('MAIL_PASSWORD');
            
            if (!$username || !$password) {
                throw new \Exception('Brevo SMTP credentials are not set.');
            }
            
            return new BrevoSmtpTransport(
                $username,
                $password
            );
        });
    }
} 