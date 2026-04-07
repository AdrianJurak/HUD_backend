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
    public function index(Request $request)
    {
        $query = Theme::with('user:id,name,profile_picture_url', 'categories:id,name')
                            ->withCount(['reviews','downloads','favoritedBy']);

        $query->when($request->search, function ($q, $search) {
            $q->where('title','like','%'.$search.'%')
            ->orWhere('description','like','%'.$search.'%');
        });

        $query->when($request->boolean('favorites'), function ($q) {
            abort_if(!auth()->check(), 401,"Must be logged in to view this content.");

            $q->whereHas('favoritedBy', function ($subQuery) {
                $subQuery->where('users.id', auth()->id());
            });
        });

        $query->when($request->categories, function ($q, $categories) {
            $categoriesArray = is_array($categories) ? $categories : explode(',', $categories);

            $q->whereHas('categories', function ($subQuery) use ($categoriesArray) {
                $subQuery->whereIn('categories.name', $categoriesArray);
            });
        });

        $sort = $request->sort ?? 'recent';

        if($sort == 'downloads'){
            $query->orderByDesc('downloads_count');
        }else if($sort == 'reviews'){
            $query->orderByDesc('reviews_count');
        }else if($sort == 'likes') {
            $query->orderByDesc('favorited_by_count');
        }else $query->latest();



        $themes = $query->paginate(15);

        return ThemeApiResource::collection($themes);
    }

    public function show($hash_id){
        $id = Theme::decodeId($hash_id);

        $theme = Theme::with('user:id,name,profile_picture_url','categories:id,name')
            ->withCount(['reviews','downloads','favoritedBy'])
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
            'categories' => 'array',
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

        if($request->has('categories')) {
            $theme->categories()->sync($request->categories);
        }

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
            'categories' => 'sometimes|array',
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

        if($request->has('categories')) {
            $theme->categories()->sync($request->categories);
        }

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

        return response()->json(["Theme removed"], 200);
    }
}
