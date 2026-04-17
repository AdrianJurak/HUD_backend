<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'profile_picture_url' => 'sometimes|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

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

        $hashedUser = new UserResource($user);

        return response()->json([
            'success' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => $hashedUser,
        ]);
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();

        if (!empty($user->profile_picture_url)) {
            Storage::disk('public')->delete($user->profile_picture_url);
        }

        $user->delete();

        return response()->json(['User removed'], 200);
    }
}
