<?php

use App\Http\Controllers\Api\PlayerController;
use Illuminate\Support\Facades\Route;

$defaultAdminPortalPath = 'portal-'.substr(hash('sha256', (string) config('app.key')), 0, 20);
$adminPortalPath = trim((string) env('ADMIN_PORTAL_PATH', $defaultAdminPortalPath), '/');

Route::get('/', function () {
    return view('welcome');
});

Route::view('/'.$adminPortalPath, 'admin')
    ->middleware('throttle:20,1')
    ->name('admin.portal');

Route::get('/admin', function () {
    abort(404);
});

Route::any('/admin/{any}', function () {
    abort(404);
})->where('any', '.*');

Route::get('/player/{device_key}', [PlayerController::class, 'show']);
