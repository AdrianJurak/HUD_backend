<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->hash_id,
            'title' => $this->title,
            'description' => $this->description,
            'layout_config' => $this->layout_config,

            'images' => collect($this->images)->map(function($path){
                return asset("storage/".$path);
            })->toArray(),

            'likes' => $this->likes,
            'reviews_count' => $this->reviews_count,
            'downloads_count' => $this->downloads_count,

            'user' => [
                'id' => $this->user->id,
                'name'=> $this->user->name,
                'profile_picture_url' => $this->user->profile_picture_url
                ? asset('storage/'.$this->user->profile_picture_url)
                : null,
            ],

            'categories' => [
                'name' => $this->categories->pluck('name'),
            ]
        ];
    }
}
