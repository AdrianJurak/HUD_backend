<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

trait HasHashids
{
    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }
    public static function decodeId($hash)
    {
        $decoded = Hashids::decode($hash);
        return $decoded[0] ?? null;
    }
}
