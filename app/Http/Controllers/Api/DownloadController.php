<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function __invoke(Theme $theme){
        $user = auth()->user();

        $alreadyDownloaded = $theme->downloads()->where('user_id', $user->id)->exists();

        if(!$alreadyDownloaded){
          $theme->downloads()->create([
              'user_id' => $user->id,
          ]);

          return response()->json(['message' => 'Theme downloaded'], 201);
        }

        return response()->json(['message' => 'Theme already downloaded'], 200);
    }
}
