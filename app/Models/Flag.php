<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flag extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'reported_user_id',
        'theme_id',
        'review_id',
        'reason',
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
