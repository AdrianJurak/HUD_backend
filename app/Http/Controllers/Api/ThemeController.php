<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Theme\DestroyThemeRequest;
use App\Http\Requests\Theme\StoreThemeRequest;
use App\Http\Requests\Theme\UpdateThemeRequest;
use App\Http\Resources\Api\Theme\IndexResource;
use App\Http\Resources\Api\Theme\ShowResource;
use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Http\Request;


class ThemeController extends Controller
{
    private ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index(Request $request)
    {
        $themes = $this->themeService->getFilteredThemes($request->all());

        return IndexResource::collection($themes);
    }

    public function show(Theme $theme)
    {
        $theme->load('user:id,name,profile_picture_url', 'categories:id,name')
            ->loadCount(['reviews', 'favoritedBy', 'downloads']);

        return new ShowResource($theme);
    }

    public function store(StoreThemeRequest $request)
    {
        $theme = $this->themeService->createTheme(
            $request->validated(),
            $request->user(),
            $request->file('images')
        );

        return response()->json([
            'message' => 'Theme created successfully',
            'id' => $theme->hash_id
        ], 201);
    }

    public function update(UpdateThemeRequest $request, Theme $theme)
    {
        $theme->load('categories:id,name');

        $this->themeService->updateTheme(
            $theme,
            $request->validated(),
            $request->file('images')
        );

        return response()->noContent();
    }

    public function destroy(DestroyThemeRequest $request, Theme $theme)
    {
        $this->themeService->deleteTheme($theme);

        return response()->noContent();
    }
}
