<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function __invoke(Theme $theme)
    {
        $download = $theme->downloads()->firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $isNew = $download->wasRecentlyCreated;

        return response()->json([
            'message' => $isNew ? 'Theme downloaded' : 'Theme already downloaded',
        ], $isNew ? 201 : 200);
    }
}
