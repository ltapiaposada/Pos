<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlatformPasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $url) {}

    public function build(): self
    {
        return $this
            ->subject('Restablece tu contraseña · Administración Adanis')
            ->view('emails.platform-password-reset')
            ->text('emails.platform-password-reset-text');
    }
}
