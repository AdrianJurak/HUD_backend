<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function update(array $validatedData): void
    {
        $uploadedFile = null;
        $oldPicture = null;
      
        $user = auth()->user();
      
        if(isset($validatedData['verification_token'], $validatedData['password'])){
           $this->changePassword($user, $validatedData['verification_token'], $validatedData['password']);
        }

        try{
            DB::transaction(function () use ($user,$validatedData, &$uploadedFile, &$oldPicture){
                

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

    private function changePassword($user, string $token, string $password): void
    {
        if (!hash_equals((string)$user->verification_token, $token)) {
            throw ValidationException::withMessages([
                'verification_token' => 'The token is invalid.'
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
