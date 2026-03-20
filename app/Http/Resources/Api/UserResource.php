<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        {
            return [
                    'id' => $this->hash_id,
                    'name'=> $this->name,
                    'email'=> $this->email,
                    'profile_picture_url' => $this->profile_picture_url
                        ? asset('storage/'.$this->profile_picture_url)
                        : null
            ];
        }
    }
}
