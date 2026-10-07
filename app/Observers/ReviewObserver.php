<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->updateThemeStats($review);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        if($review->wasChanged("avg_rating")) {
            $this->updateThemeStats($review);
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        $this->updateThemeStats($review);
    }

    private function updateThemeStats(Review $review): void
    {
        $parentId = $review->theme_id;

        $stats = Review::where("theme_id", $parentId)
            ->selectRaw("COUNT(*) as count, AVG(rating) as avg_rating")
            ->first();

        $theme = $review->theme;

        if($theme){
            $theme->avg_rating = $stats->avg_rating ?? 0.00;
            $theme->review_count = $stats->count ?? 0;
            $theme->save();
        }
    }
}
