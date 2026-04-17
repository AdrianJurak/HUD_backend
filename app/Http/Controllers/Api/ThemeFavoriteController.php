<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;

class ThemeFavoriteController extends Controller
{
    public function toggle(Theme $theme){
        $result = auth()->user()->favoriteThemes()->toggle($theme);

        $isFavorite = count($result['attached'])>0;

        return response()->json(['message'=> $isFavorite ? 'Added to favorites' : 'Removed from favorites', 'is_favorite'=>$isFavorite], 201);
    }
}
