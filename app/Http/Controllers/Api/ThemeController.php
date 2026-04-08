<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyThemeRequest;
use App\Http\Resources\Api\ThemeApiResource;
use App\Http\Resources\Api\ThemeShowResource;
use App\Http\Requests\UpdateThemeRequest;
use App\Http\Requests\StoreThemeRequest;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use App\Models\Theme;


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

        return ThemeApiResource::collection($themes);
    }

    public function show(Theme $theme)
    {
        $theme->load('user:id,name,profile_picture_url', 'categories:id,name')
            ->loadCount(['reviews','favoritedBy','downloads']);

        return new ThemeShowResource($theme);
    }

    public function store(StoreThemeRequest $request)
    {
        $theme= $this->themeService->createTheme(
            $request->validated(),
            $request->user(),
            $request->file('images')
        );

        return response()->json($theme, 201);
    }

    public function update(UpdateThemeRequest $request, Theme $theme){

        $theme->load( 'categories:id,name');

        $theme = $this->themeService->updateTheme(
            $theme,
            $request->validated(),
            $request->file('images')
        );

        return response()->json($theme, 201);
    }

    public function destroy(DestroyThemeRequest $request,Theme $theme){
        $this->themeService->deleteTheme($theme);

        return response()->json(["Theme removed"], 200);
    }
}
