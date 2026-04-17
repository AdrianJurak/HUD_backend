<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerification\TokenRefreshRequest;
use App\Http\Requests\EmailVerification\VerifyEmailRequest;
use App\Services\EmailVerificationService;
class EmailVerificationController extends Controller
{
    public function __construct(private EmailVerificationService $emailVerificationService)
    {}

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $token = $this->emailVerificationService->verifyEmail($request->validated());

        return response()->json(['message' => 'Email verified successfully',
            'token' => $token]);
    }

    public function tokenRefresh(TokenRefreshRequest $request)
    {
        $this->emailVerificationService->tokenRefresh($request->validated());

        return response()->json(['message' => 'Verification code sent']);
    }
}
