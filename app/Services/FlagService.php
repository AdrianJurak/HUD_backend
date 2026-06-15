<?php

namespace App\Services;

use App\Models\Flag;
use App\Models\Review;
use App\Models\Theme;
use App\Models\User;

class FlagService
{
    public function flag(array $validatedData): void
    {
        $reporterId = auth()->id();

        $decodedThemeId = isset($data['theme_id']) ? Theme::decodeId($data['theme_id']) : null;
        $decodedUserId = isset($data['reported_user_id']) ? User::decodeId($data['reported_user_id']) : null;
        $decodedReviewId = isset($data['review_id']) ? Review::decodeId($data['review_id']) : null;

        abort_if($decodedUserId === $reporterId, 422, 'You cannot report yourself');

        $alreadyFlagged = Flag::alreadyFlagged($reporterId, $decodedThemeId, $decodedUserId, $decodedReviewId)->exists();

        abort_if($alreadyFlagged, 422, 'You have already sent a report!');

        Flag::create([
            'user_id' => $reporterId,
            'theme_id' => $decodedThemeId,
            'reported_user_id' => $decodedUserId,
            'review_id' => $decodedReviewId,
            'reason' => $data['reason'] ?? null,
        ]);
    }
}
