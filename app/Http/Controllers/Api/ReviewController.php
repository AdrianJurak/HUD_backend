<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ReviewResource;
use Illuminate\Http\Request;
use App\Models\Theme;
use App\Models\Review;

class ReviewController extends Controller
{

    public function index($hashed_theme_id){
        $theme_id = Theme::decodeId($hashed_theme_id);

        $reviews = Review::with('user:id,name,profile_picture_url')
                                ->where('theme_id', $theme_id)
                                ->paginate(20);

        return ReviewResource::collection($reviews);
    }
    public function store(Request $request, $hashed_theme_id)
    {
        $theme_id = Theme::decodeId($hashed_theme_id);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|required|string|max:255',
            'comment' => 'nullable|required|string|max:1000'
        ]);

        $theme = Theme::findOrFail($theme_id);

        $review = $theme->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment
            ]
        );

        return response()->json([
            "status" => "success",
            'message' => 'Review is saved successfully.',
            'review' => $review
        ],201);
    }
}
