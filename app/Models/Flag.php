<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;
use App\Models\Theme;
use App\Models\User;

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

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function theme(){
        return $this->belongsTo(Theme::class);
    }

    public function review(){
        return $this->belongsTo(Review::class);
    }
}
