<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasHashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Theme;
use App\Models\Review;
use App\Models\Download;
use App\Models\Flag;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasHashids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture_url',
        'role',
        'is_banned',
        'verification_token',
        'verification_token_expires_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verification_token_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    public static function generateVerificationToken(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function flags(): HasMany
    {
        return $this->hasMany(Flag::class);
    }

    public function receivedFlags(): HasMany
    {
        return $this->hasMany(Flag::class, 'reported_user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favoriteThemes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class, 'theme_user', 'user_id', 'theme_id')->withTimestamps();
    }
}
