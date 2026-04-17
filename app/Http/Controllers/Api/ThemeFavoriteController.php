<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ThemeApiResource;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeFavoriteController extends Controller
{
    public function toggle(Theme $theme){
        $result = auth()->user()->favoriteThemes()->toggle($theme);

        $isFavorite = count($result['attached'])>0;

        return response()->json(['message'=> $isFavorite ? 'Added to favorites' : 'Removed from favorites', 'is_favorite'=>$isFavorite], 201);
    }
}
