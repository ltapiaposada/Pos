<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $url) {}

    public function build(): self
    {
        return $this->subject('Actualiza tu contraseña')->view('emails.password-reset');
    }
}
