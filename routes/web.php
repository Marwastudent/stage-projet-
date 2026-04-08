<?php

use App\Http\Controllers\Api\PlayerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/admin', 'admin');
Route::redirect('/admin/login', '/admin');
Route::redirect('/admin/dashboard', '/admin');

Route::get('/player/{device_key}', [PlayerController::class, 'show']);
