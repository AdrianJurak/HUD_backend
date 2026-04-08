<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FlagController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ThemeFavoriteController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');

    Route::post('verify', [EmailVerificationController::class, 'verifyEmail'])->middleware('throttle:5,1');
    Route::post('token-refresh', [EmailVerificationController::class, 'tokenRefresh'])->middleware('throttle:5,1');

    Route::post('generate-password-token', [PasswordResetController::class, 'passwordRecoveryToken'])->middleware('throttle:5,1');
    Route::post('password-change', [PasswordResetController::class, 'passwordChange'])->middleware('throttle:5,1');

    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('themes', [ThemeController::class, 'index']);
    Route::get('themes/{theme}', [ThemeController::class, 'show']);

    Route::get('categories', function () {
        $categories = Category::all(['id', 'name']);

        return response()->json($categories, 200);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('user', function (Request $request) {
            $user = $request->user();

            return response()->json([
                'id' => $user->hash_id,
                'name' => $user->name,
                'email' => $user->email,

                'profile_picture_url' => $user->profile_picture_url
                    ? asset('storage/' . $user->profile_picture_url)
                    : null,
            ]);
        });
        Route::put('profile', [ProfileController::class, 'update']);
        Route::delete('profile', [ProfileController::class, 'destroy']);

        Route::get('themes/{theme}/reviews', [ReviewController::class, 'index']);
        Route::post('themes/{theme}/reviews', [ReviewController::class, 'store']);
        Route::delete('themes/{theme}/reviews', [ReviewController::class, 'destroy']);

        Route::post('themes/{theme}/downloads', DownloadController::class);

        Route::post('flags', [FlagController::class, 'store']);

        Route::post('themes', [ThemeController::class, 'store']);
        Route::put('themes/{theme}', [ThemeController::class, 'update']);
        Route::delete('themes/{theme}', [ThemeController::class, 'destroy']);

        Route::post('themes/{theme}/favorite', [ThemeFavoriteController::class, 'toggle']);
    });

});
