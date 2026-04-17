<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Review\IndexResource;
use App\Models\Review;
use App\Models\Theme;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

    public function index(Theme $theme)
    {
        $reviews = $theme->reviews()
            ->with('user:id,name,profile_picture_url')
            ->paginate(20);

        return IndexResource::collection($reviews);
    }

    public function store(Request $request, Theme $theme)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000'
        ]);

        $review = $theme->reviews()->updateOrCreate(
            ['user_id' => auth()->user()->id],
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
        ], 201);
    }

    public function destroy(Theme $theme, Review $review)
    {
        if (auth()->id() != $review->user_id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review removed'], 200);
    }
}
