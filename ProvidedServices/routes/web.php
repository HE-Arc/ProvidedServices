<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');  // Protection de la route avec le middleware auth

Route::get('/login', function () {
    return view('login'); // Vue de login VueJS
})->name('login');

Route::post('api/register', [AuthController::class, 'register']);
Route::post('api/login', [AuthController::class, 'login']);

Route::get('/api/auth-check', function () {
    return response()->json(['authenticated' => Auth::check()]);
});

Route::post('/logout', function () {
    Auth::logout();
    return response()->json(['message' => 'Logout successful']);
})->name('logout');

Route::get('/api/user', function () {
    return response()->json(Auth::user());
})->middleware('auth');

Route::get('/profile/{id}', [ProfileController::class, 'show'])->middleware('auth');

Route::put('/api/profile/{id}/update', [ProfileController::class, 'update'])->middleware('auth');

Route::post('/api/profile/{id}/upload-cv', [ProfileController::class, 'uploadCv'])->middleware('auth');

Route::get('/api/profile/{user_id}/cv', [ProfileController::class, 'getCv'])->middleware('auth');
