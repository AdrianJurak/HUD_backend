<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flag\StoreRequest;
use App\Services\FlagService;
use Illuminate\Http\JsonResponse;

class FlagController extends Controller
{
    public function store(StoreRequest $request): JsonResponse
    {
        FlagService::flag($request->validated());

        return response()->json(['message' => 'The report has been submitted.'], 201);
    }
}
