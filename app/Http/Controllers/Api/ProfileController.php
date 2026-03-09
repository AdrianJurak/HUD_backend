<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'profile_picture_url' => 'sometimes|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        if ($request->hasFile('name')) {
            $user->name = $request->name;
        }

        if ($request->hasFile('profile_picture_url')) {
            if($user->profile_picture_url){
                Storage::disk('public')->delete($user->profile_picture_url);
            }

            $path = $request->file('profile_picture_url')->store('profile_pictures', 'public');

            $user->profile_picture_url = $path;
        }

        $user->save();

        return response()->json([
            'success' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }
}
