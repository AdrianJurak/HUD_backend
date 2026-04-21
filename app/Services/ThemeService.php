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

        $wantsFavorites = filter_var($data['favorites'] ?? false, FILTER_VALIDATE_BOOLEAN);
        abort_if($wantsFavorites && !auth()->check(), 401, 'Must be logged in');

        return Theme::query()
            ->with('user:id,name,profile_picture_url', 'categories:id,name')
            ->withCount(['reviews', 'downloads', 'favoritedBy'])
            ->search($data['search'] ?? null)
            ->filterByCategories($data['categories'] ?? null)
            ->when($wantsFavorites, fn($q) => $q->favoritedByUser(auth()->id()))
            ->applySort($data['sort'] ?? 'recent')
            ->paginate(15);
    }

    public function createTheme(array $data, $user, $images = null): Theme
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

    public function updateTheme(Theme $theme, array $data, $images = null): Theme
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

    public function deleteTheme(Theme $theme): void
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

    public function deleteImages(?array $images): void
    {
        if (!empty($images)) {
            Storage::disk('public')->delete($images);
        }
    }
}
