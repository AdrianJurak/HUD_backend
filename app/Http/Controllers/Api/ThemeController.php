<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Theme\DestroyThemeRequest;
use App\Http\Requests\Theme\IndexRequest;
use App\Http\Requests\Theme\StoreThemeRequest;
use App\Http\Requests\Theme\UpdateThemeRequest;
use App\Http\Resources\Api\Theme\IndexResource;
use App\Http\Resources\Api\Theme\ShowResource;
use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;


class ThemeController extends Controller
{
    public function __construct(private ThemeService $themeService){}

    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $themes = $this->themeService->getFilteredThemes($request->validated());

        return IndexResource::collection($themes);
    }

    public function show(Theme $theme): ShowResource
    {
        $theme->load('user:id,name,profile_picture_url', 'categories:id,name')
            ->loadCount(['reviews', 'favoritedBy', 'downloads']);

        return new ShowResource($theme);
    }

    public function store(StoreThemeRequest $request): JsonResponse
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

    public function update(UpdateThemeRequest $request, Theme $theme): Response
    {
      $this->themeService->updateTheme(
            $theme,
            $request->validated(),
            $request->file('images')
        );

        return response()->noContent();
    }

    public function destroy(DestroyThemeRequest $request, Theme $theme): Response
    {
        $this->themeService->deleteTheme($theme);

        return response()->noContent();
    }
}
