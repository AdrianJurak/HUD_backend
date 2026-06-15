<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    public function passwordResetToken(array $validatedData): void
    {
        $user = User::where('email', $validatedData['email'])->first();

        abort_if(! $user, 404, 'User not found.');
        abort_if($user->email_verified_at === null, 400, 'Email is unverified.');
        abort_if($user->verification_token_expires_at > now(), 400, 'Previous token is still valid');

        $token = User::generateVerificationToken();

        try {
            DB::transaction(function () use (&$user, &$token) {

                $user->update([
                    'verification_token' => $token,
                    'verification_token_expires_at' => now()->addMinutes(10),
                ]);

            });
        } catch (\Throwable $th) {
            Log::error('Password reset transaction failed: '.$th->getMessage());
            abort(500, 'Wystąpił błąd podczas resetu hasła. Spróbuj ponownie.');
        }

        Mail::to($user->email)->queue(new VerificationCodeMail($token));
    }

    public function passwordChange(array $validatedData): void
    {
        $user = User::where('email', $validatedData['email'])->first();

        abort_if(! $user, 404, 'User not found.');
        abort_if(! hash_equals((string) $user->verification_token, (string) $validatedData['verification_token']), 400, 'Invalid verification code.');
        abort_if($user->verification_token_expires_at < now() || $user->verification_token_expires_at == null, 400, 'Previous verification code is expired.');

        $user->update([
            'password' => Hash::make($validatedData['password']),
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);
    }
}
