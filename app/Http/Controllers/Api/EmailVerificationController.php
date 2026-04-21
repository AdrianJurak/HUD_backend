<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerification\TokenRefreshRequest;
use App\Http\Requests\EmailVerification\VerifyEmailRequest;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function __construct(private EmailVerificationService $emailVerificationService){}

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $token = $this->emailVerificationService->verifyEmail($request->validated());

        return response()->json(['message' => 'Email verified successfully',
            'token' => $token]);
    }

    public function tokenRefresh(TokenRefreshRequest $request): JsonResponse
    {
        $this->emailVerificationService->tokenRefresh($request->validated());

        return response()->json(['message' => 'Verification code sent']);
    }
}
