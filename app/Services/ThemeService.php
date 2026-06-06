<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $uploadedFiles = [];

        try{
         return DB::transaction(function () use ($data, $user, $images, &$uploadedFiles) {
             if ($images) {
                 $paths = $this->uploadImages($images);

                 $uploadedFiles = $paths;
                 $data['images'] = $paths;
             }

             $theme = $user->themes()->create($data);

             if (isset($data['categories'])) {
                 $theme->categories()->sync($data['categories']);
             }

             return $theme;
         });

        }catch(\Throwable $th){
            if(!empty($uploadedFiles)){
                foreach($uploadedFiles as $file){
                    Storage::disk('public')->delete($file);
                }
            }

            Log::error('Profile update transaction failed: ' . $th->getMessage());
            abort(500, 'Wystąpił błąd. Spróbuj ponownie.');
        }
    }

    public function updateTheme(Theme $theme, array $data, $images = null): Theme
    {
        $newUploadedFiles = [];
        $oldFilesToDelete = [];

        try {
            DB::transaction(function () use ($theme, $data, $images, &$newUploadedFiles, &$oldFilesToDelete) {

                if ($images) {
                    if ($theme->images) {
                        $oldFilesToDelete = $theme->images;
                    }

                    $newUploadedFiles = $this->uploadImages($images);

                    $data['images'] = $newUploadedFiles;
                }

                if (isset($data['categories'])) {
                    $categoriesToSync = $data['categories'];
                    unset($data['categories']);
                    $theme->categories()->sync($categoriesToSync);
                }

                $theme->update($data);
            });
            if (!empty($oldFilesToDelete)) {
                foreach ($oldFilesToDelete as $oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            return $theme->fresh();

        } catch (\Throwable $th) {
            if (!empty($newUploadedFiles)) {
                foreach ($newUploadedFiles as $file) {
                    Storage::disk('public')->delete($file);
                }
            }

            Log::error('Theme update transaction failed: ' . $th->getMessage());
            abort(500, 'Wystąpił błąd podczas aktualizacji motywu. Spróbuj ponownie.');
        }
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

    public function deleteImages(?array $images): array
    {
        $imagePaths = [];

        if (!empty($images)) {
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
                $imagePaths[] = $image;
            }
        }

        return $imagePaths;
    }
}
