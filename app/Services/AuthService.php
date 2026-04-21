<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['verification_token'] = $this->createToken();
        $data['verification_token_expires_at'] = now()->addMinutes(10);

        $user = User::create($data);

        Mail::to($user->email)->queue(new VerificationCodeMail($data['verification_token']));

        return $user;
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            abort(401,'Your credentials are incorrect');
        }

        if ($user->email_verified_at == null) {
            abort(400,'Email is not verified.');
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function createToken() :String
    {
        return User::generateVerificationToken();
    }
}
