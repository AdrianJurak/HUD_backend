<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function update(array $validatedData): void
    {
        $uploadedFile = null;
        $oldPicture = null;

        try{
            DB::transaction(function () use ($validatedData, &$uploadedFile, &$oldPicture){
                $user = auth()->user();

                if (isset($validatedData['name'])) {
                    $user->name = $validatedData['name'];
                }

                if (isset($validatedData['profile_picture_url'])) {
                    if ($user->profile_picture_url) {
                        $oldPicture = $user->profile_picture_url;
                    }

                    $path = $validatedData['profile_picture_url']->store('profile_pictures', 'public');

                    $uploadedFile = $path;

                    $user->profile_picture_url = $path;
                }

                $user->save();
            });

            if($oldPicture){
                Storage::disk('public')->delete($oldPicture);
            }

        }catch(\Throwable $th){
            if($uploadedFile){
                Storage::disk('public')->delete($uploadedFile);
            }

            Log::error('Profile update transaction failed: ' . $th->getMessage());
            abort(500, 'Wystąpił błąd. Spróbuj ponownie.');
        }
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
