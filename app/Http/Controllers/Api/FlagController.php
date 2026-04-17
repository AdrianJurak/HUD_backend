<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flag;
use App\Models\Review;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;

class FlagController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'reason' => 'nullable|string|max:255',
            'theme_id' => 'required_without_all:reported_user_id,review_id|prohibits:reported_user_id,review_id',
            'reported_user_id' => 'required_without_all:theme_id,review_id|prohibits:theme_id,review_id',
            'review_id' => 'required_without_all:theme_id,reported_user_id|prohibits:theme_id,reported_user_id',
        ]);

        $reporterId = auth()->id();

        $decodedThemeId = ($validatedData['theme_id'] ?? null) ? Theme::decodeId($validatedData['theme_id']) : null;
        $decodedUserId = ($validatedData['reported_user_id'] ?? null) ? User::decodeId($validatedData['reported_user_id']) : null;
        $decodedReviewId = ($validatedData['review_id'] ?? null) ? Review::decodeId($validatedData['review_id']) : null;

        if ($decodedUserId != null && $decodedUserId == $reporterId) {
            return response()->json(['message' => 'You cannot report yourself'], 422);
        }

        $alreadyFlagged = Flag::where('user_id', $reporterId)
            ->when($decodedThemeId, function ($query) use ($decodedThemeId) {
                return $query->where('theme_id', $decodedThemeId);
            })
            ->when($decodedUserId, function ($query) use ($decodedUserId) {
                return $query->where('reported_user_id', $decodedUserId);
            })
            ->when($decodedReviewId, function ($query) use ($decodedReviewId) {
                return $query->where('review_id', $decodedReviewId);
            })
            ->exists();

        if ($alreadyFlagged) {
            return response()->json(['message' => 'You have already sent a report!'], 422);
        }

        Flag::create([
            'user_id' => $reporterId,
            'theme_id' => $decodedThemeId,
            'reported_user_id' => $decodedUserId,
            'review_id' => $decodedReviewId,
            'reason' => $validatedData['reason'] ?? null,
        ]);

        return response()->json(['message' => 'The report has been submitted.'], 201);
    }
}
