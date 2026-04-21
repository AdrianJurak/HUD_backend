<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    public function verifyEmail(array $data): string
    {
        $user = User::where('email', $data['email'])->first();

        abort_if(!$user, 404, 'User not found');
        abort_if($user->email_verified_at != null, 400, 'Email already verified');
        abort_if($user->verification_token != $data['verification_token'], 400, 'Invalid verification code.');
        abort_if($user->verification_token_expires_at < now(), 400, 'Verification token expired');

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        $user->save();

        $token = $user->createToken('android-app')->plainTextToken;

        return $token;
    }

    public function tokenRefresh(array $data): string
    {
        $user = User::where('email', $data['email'])->first();

        abort_if(!$user, 404, 'User not found');
        abort_if($user->verification_token_expires_at > now(), 400, 'Current code is still valid try again later');

        $token = User::generateVerificationToken();

        $user->update([
            'verification_token' => $token,
            'verification_token_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($token));

        return $token;
    }
}
