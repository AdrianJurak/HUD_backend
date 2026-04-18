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

    public function resolveRouteBinding($value, $field = null)
    {
        $decodeId = self::decodeId($value);

        if (!$decodeId) {
            return null;
        }

        return $this->where('id', $decodeId)->firstOrFail();
    }

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, fn($q) =>
            $q->where(fn($subQuery) =>
                $subQuery->where('title', 'like', '%' . $search . '%')
                         ->orWhere('description', 'like', '%' . $search . '%')
            )
        );
    }

    public function scopeFavoritedByUser($query, ?int $user_id)
    {
        return $query->when($user_id, fn($q) =>
            $q->whereHas('favoritedBy', fn($subQuery) =>
                $subQuery->where('user_id', $user_id)
            )
        );
    }

    public function scopeFilterByCategories($query, $categories)
    {
        return $query->when($categories, function ($q) use ($categories) {
            $categoriesArray = is_array($categories) ? $categories : explode(',', $categories);

            $q->whereHas('categories', function ($subQuery) use ($categoriesArray) {
                $subQuery->whereIn('categories.name', $categoriesArray);
            });
        });
    }

    public function scopeApplySort($query, ?string $sort)
    {
        return match ($sort) {
          'downloads' => $query->orderByDesc('downloads_count'),
          'reviews' => $query->orderByDesc('reviews_count'),
          'likes' =>  $query->orderByDesc('favorited_by_count'),
          default => $query->latest(),
        };
    }

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
