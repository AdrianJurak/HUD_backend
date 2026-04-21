<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

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

        $user->save();
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
