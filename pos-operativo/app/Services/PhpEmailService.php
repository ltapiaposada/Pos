<?php

namespace App\Services;

use App\Mail\PasswordResetEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PhpEmailService
{
    public function sendPasswordReset(User $user): void
    {
        $token = Password::broker()->createToken($user);
        $url = url('/reset-password/'.$token.'?email='.urlencode($user->email));
        Mail::to($user->email)->send(new PasswordResetEmail($user, $url));
    }
}
