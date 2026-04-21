<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateRequest;
use App\Http\Resources\Api\User\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService){}

    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
    public function update(UpdateRequest $request): JsonResponse
    {
        $this->profileService->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function destroy(Request $request): Response
    {
        $this->profileService->delete();

        return response()->noContent();
    }
}
