<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MagicLoginLink extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * User model
     *
     * @var User
     */
    protected $user;

    /**
     * Login token
     *
     * @var string
     */
    protected $token;

    /**
     * Token expiration time
     *
     * @var string
     */
    protected $validUntil;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $token
     * @param string $validUntil
     */
    public function __construct(User $user, $token, $validUntil)
    {
        $this->user = $user;
        $this->token = $token;
        $this->validUntil = $validUntil;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Login Link to ' . config('app.name'))
                    ->view('emails.magic-link')
                    ->with([
                        'user' => $this->user,
                        'token' => $this->token,
                        'validUntil' => $this->validUntil
                    ]);
    }
} 