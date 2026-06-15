<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordReset\PasswordChangeRequest;
use App\Http\Requests\PasswordReset\PasswordRecoveryTokenRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService) {}

    public function passwordRecoveryToken(PasswordRecoveryTokenRequest $request): JsonResponse
    {
        $this->passwordResetService->passwordResetToken($request->validated());

        return response()->json(['message' => 'Verification code sent.']);
    }

    public function passwordChange(PasswordChangeRequest $request): JsonResponse
    {
        $this->passwordResetService->passwordChange($request->validated());

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
