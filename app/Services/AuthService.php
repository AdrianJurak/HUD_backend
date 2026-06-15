<?php

namespace App\Services;

use App\Http\Resources\Api\User\UserResource;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['verification_token'] = $this->createToken();
        $data['verification_token_expires_at'] = now()->addMinutes(10);
        try {
            return DB::transaction(function () use ($data) {
                $user = User::create($data);

                Mail::to($user->email)->queue(new VerificationCodeMail($data['verification_token']));

                return new UserResource($user);
            });
        } catch (\Throwable $th) {
            Log::error('Registration transaction failed: '.$th->getMessage());
            abort(500, 'Wystąpił błąd podczas rejestracji. Spróbuj ponownie.');
        }

    }

    public function login(array $data): array
    {
        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            abort(401, 'Your credentials are incorrect');
        }

        $user = Auth::user();

        if ($user->email_verified_at == null) {
            abort(400, 'Email is not verified.');
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token,
        ];
    }

    public function createToken(): string
    {
        return User::generateVerificationToken();
    }
}
