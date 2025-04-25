<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLoginLink;
use App\Models\LoginToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MagicLinkController extends Controller
{
    /**
     * Display the magic link login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.magic-login');
    }

    /**
     * Send a login link to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We could not find a user with that email address.'
            ]);
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

        Log::info('Magic link requested', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token_expiry' => $expiresAt->format('Y-m-d H:i:s')
        ]);

        // Send the email
        try {
            Mail::to($user)->send(new MagicLoginLink(
                $user,
                $token,
                $expiresAt->format('Y-m-d H:i:s')
            ));
            
            Log::info('Magic link email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send magic link email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }

        return back()->with('status', 'We\'ve sent a magic link to your email!');
    }

    /**
     * Login the user using a valid token.
     *
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login($token)
    {
        Log::info('Attempting to login with token', ['token' => $token]);
        
        $loginToken = LoginToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->first();

        if (!$loginToken) {
            Log::warning('Invalid or expired token used', ['token' => $token]);
            return redirect()->route('login.magic')
                ->withErrors(['token' => 'Invalid or expired token.']);
        }

        // Mark the token as used
        $loginToken->update(['used' => true]);

        try {
            // Login the user
            Auth::login($loginToken->user);
            
            // Generate an API token for the user (if Passport is available)
            try {
                $apiToken = $loginToken->user->createToken('auth_token')->accessToken;
                Log::info('User logged in successfully with magic link', [
                    'user_id' => $loginToken->user->id,
                    'email' => $loginToken->user->email
                ]);
                
                // Redirect with API token
                return redirect()->intended('/dashboard')
                    ->with('api_token', $apiToken);
            } catch (\Exception $e) {
                Log::error('Failed to create API token', [
                    'user_id' => $loginToken->user->id,
                    'error' => $e->getMessage()
                ]);
                
                // Redirect without API token
                return redirect()->intended('/dashboard')
                    ->with('status', 'Logged in successfully, but API token could not be generated.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to login user with magic link', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('login.magic')
                ->withErrors(['error' => 'An error occurred during login. Please try again.']);
        }
    }
} 