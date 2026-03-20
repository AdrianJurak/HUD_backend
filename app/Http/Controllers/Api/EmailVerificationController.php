<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    public function verifyEmail(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'verification_token' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email_verified_at != null) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        if ($user->verification_token != $request->verification_token) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        if ($user->verification_token_expires_at < now()) {
            return response()->json(['message' => 'Verification code has expired.'], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);

        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json(['message' => 'Email verified successfully.',
            'token' => $token], 200);
    }

    public function tokenRefresh(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if(!$user){
            return response()->json(['message' => 'User not found.'], 404);
        }

        if($user->verification_token_expires_at > now()){
            return response()->json(['message' => 'Current code is still valid try again later'], 400);
        }

        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'verification_token' => $token,
            'verification_token_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($token));

        return response()->json(['message' => 'Verification code sent.'], 200);
    }
}
