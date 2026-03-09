<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Theme;
use App\Models\Review;
use App\Models\Download;
use App\Models\Flag;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture_url',
        'role',
        'is_banned'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    public function themes(){
        return $this->hasMany(Theme::class);
    }

    public function downloads(){
        return $this->hasMany(Download::class);
    }

    public function flags(){
        return $this->hasMany(Flag::class);
    }

    public function receivedFlags()
    {
        return $this->hasMany(Flag::class, 'reported_user_id');
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }
}
