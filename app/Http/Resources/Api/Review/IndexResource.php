<?php

namespace App\Http\Resources\Api\Review;

use App\Models\Theme;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->hash_id,
            'theme_id' => Theme::encodeId($this->theme_id),
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'created_at' => $this->created_at,

            'user' => $this->user ? [
                'id' => $this->user->hash_id,
                'name' => $this->user->name,
                'profile_picture_url' => $this->user->profile_picture_url
                    ? asset('storage/' . $this->user->profile_picture_url)
                    : null,
            ]: null,
        ];
    }
}
