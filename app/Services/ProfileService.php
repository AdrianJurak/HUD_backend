<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use function Laravel\Prompts\password;

class ProfileService
{
    public function update(array $validatedData): void
    {
        $user = auth()->user();

        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }

        if (isset($validatedData['profile_picture_url'])) {
            if ($user->profile_picture_url) {
                Storage::disk('public')->delete($user->profile_picture_url);
            }

            $path = $validatedData['profile_picture_url']->store('profile_pictures', 'public');

            $user->profile_picture_url = $path;
        }

        $this->changePassword($user, $validatedData['token'], $validatedData['password']);

        $user->save();
    }

    private function changePassword($user, string $token, string $password): void
    {
        if (!hash_equals((string)$user->verification_token, $token)) {
            throw ValidationException::withMessages([
                ['token' => ['The provided token is invalid.']],
            ]);
        }

        $user->password = Hash::make($password);
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
    }

    public function delete(): void
    {
        $user = auth()->user();

        if (!empty($user->profile_picture_url)) {
            Storage::disk('public')->delete($user->profile_picture_url);
        }

        $user->delete();
    }
}
