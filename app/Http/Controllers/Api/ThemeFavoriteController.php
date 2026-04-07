<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ThemeApiResource;
use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeFavoriteController extends Controller
{
    public function toggle($hash_id){
        $theme_id = Theme::decodeId($hash_id);

        $result = auth()->user()->favoriteThemes()->toggle($theme_id);

        $isFavorite = count($result['attached'])>0;

        return response()->json(['messege'=> $isFavorite ? 'Added to favorites' : 'Removed from favorites', 'is_favorite'=>$isFavorite], 200);
    }
}
