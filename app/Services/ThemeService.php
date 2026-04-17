<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ThemeService
{
    public function getFilteredThemes(?array $data): LengthAwarePaginator
    {
        $data = $data ?? [];

        $query = Theme::with('user:id,name,profile_picture_url', 'categories:id,name')
            ->withCount(['reviews', 'downloads', 'favoritedBy']);

        $query->when($data['search'] ?? null, function ($q, $search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        });

        $query->when(isset($data['favorites']) && filter_var($data['favorites'], FILTER_VALIDATE_BOOLEAN), function ($q) {
            abort_if(!auth()->check(), 401, "Must be logged in to view this content.");

            $q->whereHas('favoritedBy', function ($subQuery) {
                $subQuery->where('users.id', auth()->id());
            });
        });

        $query->when($data['categories'] ?? null, function ($q, $categories) {
            $categoriesArray = is_array($categories) ? $categories : explode(',', $categories);

            $q->whereHas('categories', function ($subQuery) use ($categoriesArray) {
                $subQuery->whereIn('categories.name', $categoriesArray);
            });
        });

        $sort = $data['sort'] ?? 'recent';

        if ($sort === 'downloads') {
            $query->orderByDesc('downloads_count');
        } else if ($sort === 'reviews') {
            $query->orderByDesc('reviews_count');
        } else if ($sort === 'likes') {
            $query->orderByDesc('favorited_by_count');
        } else $query->latest();


        return $query->paginate(15);
    }

    public function createTheme(array $data, $user, $images = null)
    {
        if ($images) {
            $data['images'] = $this->uploadImages($images);
        }

        $theme = $user->themes()->create($data);

        if (isset($data['categories'])) {
            $theme->categories()->sync($data['categories']);
        }

        return $theme;
    }

    public function updateTheme(Theme $theme, array $data, $images = null)
    {
        if ($images) {
            $this->deleteImages($theme->images);
            $data['images'] = $this->uploadImages($images);
        }

        if (isset($data['categories'])) {
            $categoriesToSync = $data['categories'];

            unset($data['categories']);

            $theme->categories()->sync($categoriesToSync);
        }

        $theme->update($data);

        return $theme;
    }

    public function deleteTheme(Theme $theme)
    {
        $this->deleteImages($theme->images);
        $theme->delete();
    }

    public function uploadImages($images): array
    {
        $imagePaths = [];
        foreach ($images as $image) {
            $imagePaths[] = $image->store('theme_images', 'public');
        }
        return $imagePaths;
    }

    public function deleteImages(?array $images)
    {
        if (!empty($images)) {
            Storage::disk('public')->delete($images);
        }
    }
}
