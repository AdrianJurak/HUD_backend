<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

trait HasHashids
{
    public function getHashIdAttribute()
    {
        return Hashids::connection($this->getTable())->encode($this->id);
    }
    public static function decodeId($hash)
    {
        $tableName = (new static)->getTable();

        $decoded = Hashids::connection($tableName)->decode($hash);
        return $decoded[0] ?? null;
    }
    public static function encodeId($id)
    {
        if (!$id) {
            return null;
        }

        $tableName = (new static)->getTable();

        return Hashids::connection($tableName)->encode($id);
    }
}
