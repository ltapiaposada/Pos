<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StorefrontPasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array<string, mixed> $customer */
    public function __construct(
        public array $customer,
        public string $storeName,
        public string $url,
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Restablece tu contraseña · {$this->storeName}")
            ->view('emails.storefront-password-reset')
            ->text('emails.storefront-password-reset-text');
    }
}
