<?php

namespace App\Console\Commands;

use App\Models\LoginToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateTestMagicLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magic-link:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test magic login link for the first user';

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

        // Generate token
        $token = LoginToken::generateToken();
        
        // Set expiration time (1 hour from now)
        $expiresAt = Carbon::now()->addHour();

        // Save the token
        LoginToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        $loginUrl = url('/login/magic/' . $token);

        $this->info('Created magic login link for: ' . $user->email);
        $this->info('Token: ' . $token);
        $this->info('Expires at: ' . $expiresAt->format('Y-m-d H:i:s'));
        $this->info('Login URL: ' . $loginUrl);
        
        return 0;
    }
} 