<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreRequest;
use App\Http\Resources\Api\Review\IndexResource;
use App\Models\Review;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{

    public function index(Theme $theme): AnonymousResourceCollection
    {
        $reviews = $theme->reviews()
            ->with('user:id,name,profile_picture_url')
            ->paginate(20);

        return IndexResource::collection($reviews);
    }

    public function store(StoreRequest $request, Theme $theme): JsonResponse
    {
        $theme->reviews()->updateOrCreate(
            ['user_id' => auth()->id()],
            $request->validated()
        );

        return response()->json(['message' => 'Review is saved successfully.'], 201);
    }

    public function destroy(Theme $theme, Review $review): JsonResponse
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return response()->json(['message' => 'Review removed']);
    }
}
