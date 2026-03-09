<?php

namespace App\Models;

use App\Traits\HasHashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Theme;
use App\Models\Flag;

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

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function theme(){
        return $this->belongsTo(Theme::class);
    }

    public function flags(){
        return $this->hasMany(Flag::class);
    }
}
