<?php

namespace App\Http\Resources\Api\Theme;

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
        $firstImage = !empty($this->images) ? $this->images[0] : null;
        return [
            'id' => $this->hash_id,
            'title' => $this->title,
            'images' => $firstImage ? asset('storage/'.$firstImage) : null,
            'likes_count' => $this->favorited_by_count,
            'reviews_count' => $this->reviews_count,
            'downloads_count' => $this->downloads_count,

            'user' => [
                'id' => $this->user->hash_id,
                'name'=> $this->user->name,
                'profile_picture_url' => $this->user->profile_picture_url
                    ? asset('storage/'.$this->user->profile_picture_url)
                    : null,
            ],

            'categories' => $this->categories->pluck('name'),

        ];
    }
}
