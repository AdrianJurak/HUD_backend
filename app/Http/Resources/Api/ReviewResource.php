<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
                'theme_id' => Hashids::encode($this->theme_id),
                'rating' => $this->rating,
                'title' => $this->title,
                'comment' => $this->comment,
                'created_at' => $this->created_at,

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
}
