<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flag\StoreRequest;
use App\Services\FlagService;
use Illuminate\Http\JsonResponse;

class FlagController extends Controller
{
    public function __construct(private FlagService $flagService) {}

    public function store(StoreRequest $request): JsonResponse
    {
        $reporterId = auth()->id();

        $this->flagService->flag($request->validated(), $reporterId);

        return response()->json(['message' => 'The report has been submitted.'], 201);
    }
}
