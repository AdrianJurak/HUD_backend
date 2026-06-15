<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;

class DownloadController extends Controller
{
    public function __invoke(Theme $theme): JsonResponse
    {
        $download = $theme->downloads()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        $isNew = $download->wasRecentlyCreated;

        return response()->json([
            'message' => $isNew ? 'Theme downloaded' : 'Theme already downloaded',
        ], $isNew ? 201 : 200);
    }
}
