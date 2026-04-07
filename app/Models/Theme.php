<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Review;
use App\Models\Download;
use App\Models\User;
use App\Models\Flag;
use App\Traits\HasHashids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasFactory, HasHashids;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'layout_config',
        'images'
    ];

    protected $casts = [
        'layout_config' => 'array',
        'images' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function flags(): HasMany
    {
        return $this->hasMany(Flag::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'theme_user', 'theme_id', 'user_id')->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_theme', 'theme_id', 'category_id');
    }
}
