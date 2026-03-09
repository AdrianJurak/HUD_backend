<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $firstImage = !empty($this->image) ? $this->image[0] : null;
        return [
            'id' => $this->hash_id,
            'title' => $this->title,
            'likes' => $this->likes,
            'images' => $firstImage ? asset('storage/'.$firstImage) : null,
            'reviews_count' => $this->reviews_count,
            'downloads_count' => $this->downloads_count,

            'user' => [
                'id' => $this->user->id,
                'name'=> $this->user->name,
                'profile_picture_url' => $this->user->profile_picture_url
                    ? asset('storage/'.$this->user->profile_picture_url)
                    : null,
            ]
        ];
    }
}
