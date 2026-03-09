<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Review;
use App\Models\Download;
use App\Models\User;
use App\Models\Flag;
use App\Traits\HasHashids;

class Theme extends Model
{
    use HasFactory, HasHashids;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'likes',
        'layout_config',
        'images'
    ];

    protected $casts = [
        'layout_config' => 'array',
        'images' => 'array'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function downloads(){
        return $this->hasMany(Download::class);
    }

    public function flags(){
        return $this->hasMany(Flag::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
