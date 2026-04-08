<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdScheduleController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\PlaylistItemController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\ScreenController;
use App\Http\Controllers\Api\ScreenPlaylistController;
use App\Http\Controllers\Api\SportsHallController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login/resend-otp', [AuthController::class, 'resendOtp']);
Route::get('/player/{device_key}', [PlayerController::class, 'feed']);

Route::middleware('auth.token')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('screens', ScreenController::class)->only(['index', 'show']);
    Route::apiResource('media', MediaController::class)
        ->only(['index', 'show'])
        ->parameters(['media' => 'media']);
    Route::apiResource('programs', ProgramController::class)->only(['index', 'show']);
    Route::apiResource('playlists', PlaylistController::class)->only(['index', 'show']);
    Route::apiResource('playlist-items', PlaylistItemController::class)
        ->only(['index', 'show'])
        ->parameters(['playlist-items' => 'playlist_item']);
    Route::apiResource('sports-halls', SportsHallController::class)->only(['index', 'show']);
    Route::apiResource('ad-schedules', AdScheduleController::class)->only(['index', 'show']);
    Route::apiResource('screen-playlists', ScreenPlaylistController::class)->only(['index']);

    Route::middleware('role:admin,manager')->group(function (): void {
        Route::apiResource('screens', ScreenController::class)->except(['index', 'show']);
        Route::post('/screens/{screen}/assign-playlist', [ScreenController::class, 'assignPlaylist']);
        Route::apiResource('screen-playlists', ScreenPlaylistController::class)->only(['update', 'destroy']);

        Route::apiResource('media', MediaController::class)
            ->except(['index', 'show'])
            ->parameters(['media' => 'media']);

        Route::apiResource('programs', ProgramController::class)->except(['index', 'show']);
        Route::apiResource('playlists', PlaylistController::class)->except(['index', 'show']);
        Route::put('/playlists/{playlist}/items/reorder', [PlaylistController::class, 'reorderItems']);
        Route::apiResource('sports-halls', SportsHallController::class)->except(['index', 'show']);
        Route::apiResource('ad-schedules', AdScheduleController::class)->except(['index', 'show']);

        Route::apiResource('playlist-items', PlaylistItemController::class)
            ->except(['index', 'show'])
            ->parameters(['playlist-items' => 'playlist_item']);
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::apiResource('users', UserController::class);
    });
});

