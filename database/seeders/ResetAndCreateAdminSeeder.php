<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ResetAndCreateAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the users table (reset all users)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        // Also truncate the login_tokens and passport tables to avoid orphaned records
        DB::table('login_tokens')->truncate();
        
        // Check if oauth tables exist before truncating
        if(Schema::hasTable('oauth_access_tokens')) {
            DB::table('oauth_access_tokens')->truncate();
        }
        if(Schema::hasTable('oauth_refresh_tokens')) {
            DB::table('oauth_refresh_tokens')->truncate();
        }
        if(Schema::hasTable('oauth_auth_codes')) {
            DB::table('oauth_auth_codes')->truncate();
        }
        if(Schema::hasTable('oauth_clients')) {
            DB::table('oauth_clients')->truncate();
        }
        if(Schema::hasTable('oauth_personal_access_clients')) {
            DB::table('oauth_personal_access_clients')->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create a new admin user
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'andrea.marrano92@gmail.com',
            'password' => Hash::make('admin123'), // This password won't be used with magic link login but it's required
            'email_verified_at' => now(),
        ]);

        $this->command->info('All users have been reset and an admin user has been created.');
        $this->command->info('Admin user details:');
        $this->command->info('ID: ' . $user->id);
        $this->command->info('Name: ' . $user->name);
        $this->command->info('Email: ' . $user->email);
    }
} 