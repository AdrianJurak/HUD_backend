<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordReset\PasswordRecoveryTokenRequest;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function passwordRecoveryToken(PasswordRecoveryTokenRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::where('email', $validatedData['email'])->first();

        abort_if(!$user, 404, 'User not found.');
        abort_if($user->email_verified_at, 400, 'Email is unverified.');
        abort_if($user->verification_token_expires_at > now(), 400, 'Previous token is still valid');

        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'verification_token' => $token,
            'verification_token_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($token));

        return response()->json(['message' => 'Verification code sent.'], 200);
    }

    public function passwordChange(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
            'verification_token' => 'required|string',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if(!$user){
            return response()->json(['message' => 'User not found.'], 404);
        }

        if($validatedData['verification_token'] != $user->verification_token){
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        if($user->verification_token_expires_at < now() || $user->verification_token_expires_at == null){
            return response()->json(['message' => 'Previous verification code is expired.'], 400);
        }

        $user->update([
            'password' => Hash::make($validatedData['password']),
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);

        return response()->json(['message' => 'Password updated successfully.'], 200);
    }
}
