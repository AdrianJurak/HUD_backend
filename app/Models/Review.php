<?php

namespace App\Models;

use App\Traits\HasHashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory, HasHashids;
    protected $fillable = [
        'user_id',
        'theme_id',
        'rating',
        'title',
        'comment'
    ];

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $decodeId = self::decodeId($value);

        if(!$decodeId) {
            return null;
        }

        return $this->where('id', $decodeId)->firstOrFail();
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function theme(): BelongsTo{
        return $this->belongsTo(Theme::class);
    }

    public function flags(): HasMany{
        return $this->hasMany(Flag::class);
    }
}
