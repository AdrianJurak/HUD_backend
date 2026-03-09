<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ThemeApiResource;
use App\Http\Resources\Api\ThemeShowResource;
use Illuminate\Http\Request;
use App\Models\Theme;
use Illuminate\Support\Facades\Storage;


class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::with('user:id,name,profile_picture_url')
                            ->withCount(['reviews','downloads'])
                            ->paginate(15);

        return ThemeApiResource::collection($themes);
    }

    public function show($hash_id){
        $id = Theme::decodeId($hash_id);

        $theme = Theme::with('user:id,name,profile_picture_url')
            ->withCount(['reviews','downloads'])
            ->findOrFail($id);
        return new ThemeShowResource($theme);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'layout_config' => 'required|array',
            'images' => 'nullable|array|max:5',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif|max:8192',
        ]);

        $imagePaths = [];

        if($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('theme_images', 'public');
                $imagePaths[] = $path;
            }
        }

        $validatedData['images'] = $imagePaths;

        $theme = $request->user()->themes()->create($validatedData);

        return response()->json($theme, 201);
    }

    public function update(Request $request, $hash_id){
        $id = Theme::decodeId($hash_id);

        $theme = Theme::findOrFail($id);

        if($request->user()->id !== $theme->user_id){
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'layout_config' => 'sometimes|required|array',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif|max:8192',
        ]);

        $imagePaths = [];

        if($request->hasFile('images')) {
            if(!empty($theme->images)){
                Storage::disk('public')->delete($theme->images);
            }
            foreach ($request->file('images') as $image) {
                $path = $image->store('theme_images', 'public');
                $imagePaths[] = $path;
            }
            $validatedData['images'] = $imagePaths;
        }

        $theme->update($validatedData);

        return response()->json($theme, 201);
    }

    public function destroy(Request $request,$hash_id){
        $id = Theme::decodeId($hash_id);

        $theme = Theme::findOrFail($id);

        if($request->user()->id !== $theme->user_id){
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if(!empty($theme->images)){
            Storage::disk('public')->delete($theme->images);
        }

        $theme->delete();

        return response()->json(["Theme removed"], 204);
    }
}
