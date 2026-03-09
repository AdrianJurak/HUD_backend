<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FlagController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\DownloadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/v1/register', [AuthController::class, 'register']);
Route::post('/v1/login', [AuthController::class, 'login']);

Route::get('/v1/themes', [ThemeController::class, 'index']);
Route::get('/v1/themes/{hash_id}', [ThemeController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/logout', [AuthController::class, 'logout']);

    Route::get('/v1/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/v1/profile', [ProfileController::class, 'update']);

    Route::get('/v1/themes/{hash_id}/reviews', [ReviewController::class, 'index']);
    Route::post('/v1/themes/{hash_id}/reviews', [ReviewController::class, 'store']);

    Route::post('/v1/themes/{hash_id}/downloads', DownloadController::class);

    Route::post('/v1/flags', [FlagController::class, 'store']);

    Route::post('/v1/themes', [ThemeController::class, 'store']);
    Route::put('/v1/themes/{hash_id}', [ThemeController::class, 'update']);
    Route::delete('/v1/themes/{hash_id}', [ThemeController::class, 'destroy']);
});
