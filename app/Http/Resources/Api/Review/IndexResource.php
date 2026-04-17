<?php

namespace App\Http\Resources\Api\Review;

use Illuminate\Http\Resources\Json\JsonResource;

class IndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        {
            return [
                'id' => $this->hash_id,
                'theme_id' => $this->theme->hash_id,
                'rating' => $this->rating,
                'title' => $this->title,
                'comment' => $this->comment,
                'created_at' => $this->created_at,

                'user' => [
                    'id' => $this->user->hash_id,
                    'name'=> $this->user->name,
                    'profile_picture_url' => $this->user->profile_picture_url
                        ? asset('storage/'.$this->user->profile_picture_url)
                        : null,
                ]
            ];
        }
    }
}
