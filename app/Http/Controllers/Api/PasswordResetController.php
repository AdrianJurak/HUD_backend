<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordReset\PasswordChangeRequest;
use App\Http\Requests\PasswordReset\PasswordRecoveryTokenRequest;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{

    public function __construct(private PasswordResetService $passwordResetService){}

    public function passwordRecoveryToken(PasswordRecoveryTokenRequest $request)
    {
        $this->passwordResetService->passwordResetToken($request->validated());

        return response()->json(['message' => 'Verification code sent.']);
    }

    public function passwordChange(PasswordChangeRequest $request){
        $this->passwordResetService->passwordChange($request->validated());

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
