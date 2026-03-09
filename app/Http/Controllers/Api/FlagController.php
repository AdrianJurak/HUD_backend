<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flag;
use App\Models\Theme;
use Illuminate\Http\Request;

class FlagController extends Controller
{
    public function store(Request $request){
        $validatedData = $request->validate([
            'reason' => 'nullable|max:255',
            'theme_id' => 'sometimes|nullable|exists:themes,id',
            'reported_user_id' => 'sometimes|nullable|exists:users,id',
            'review_id' => 'sometimes|nullable|exists:reviews,id',
        ]);

        $reporterId = auth()->id();

        $decodedThemeId = $request->theme_id ? Theme::decodeId($request->theme_id) : null;

        if($request->reporeted_user_id && $request->reported_user_id == $reporterId){
            return response()->json(['message' => 'You cannot report yourself'], 422);
        }

        $alreadyFlagged = flag::where('user_id', $reporterId)
        ->when($decodedThemeId, function($query) use($decodedThemeId){
            return $query->where('theme_id', $decodedThemeId);
        })
        ->when($request->reported_user_id, function($query) use($request){
            return $query->where('reported_user_id', $request->reported_user_id);
        })
        ->when($request->review_id, function($query) use($request){
            return $query->where('review_id', $request->review_id);
        })
        ->exists();

        if($alreadyFlagged){
            return response()->json(['message' => 'You have already sent a report!'], 422);
        }

        Flag::create([
            'user_id' => $reporterId,
            'theme_id' => $request->theme_id,
            'reported_user_id' => $request->reported_user_id,
            'review_id' => $request->review_id,
            'reason' => $request->reason,
        ]);

        return response()->json(['message' => 'The report has been submitted.'], 201);
    }
}
