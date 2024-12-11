<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\DashboardController;

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

Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show')->middleware('auth');

Route::put('/api/profile/{id}', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');

Route::post('/api/profile/{id}/upload-cv', [ProfileController::class, 'uploadCv'])->middleware('auth');

Route::get('/api/profile/{user_id}/cv', [ProfileController::class, 'getCv'])->middleware('auth');

Route::get('/create-offer', [JobPostController::class, 'create'])->name('create.offer')->middleware('auth');

Route::post('/api/job_posts', [JobPostController::class, 'store'])->middleware('auth');

Route::get('/api/skills', [SkillsController::class, 'index'])->middleware('auth');

Route::post('api/profile/{id}/upload-profile-picture', [ProfileController::class, 'uploadProfilePicture'])->middleware('auth');

Route::get('api/profile/{id}/profile-picture', [ProfileController::class, 'getProfilePicture'])->middleware('auth');

Route::delete('/api/profile/{id}/delete-cv', [ProfileController::class, 'deleteCv'])->middleware('auth');

Route::get('api/profile/{id}/skills', [ProfileController::class, 'getSkills'])->middleware('auth');

Route::post('api/profile/{user}/add-skill', [ProfileController::class, 'addSkill'])->middleware('auth');

Route::delete('api/profile/{user}/remove-skill', [ProfileController::class, 'removeSkill'])->middleware('auth');

Route::get('api/job-posts', [JobPostController::class, 'index']);

Route::get('api/job-posts', [JobPostController::class, 'index']);

Route::post('api/job-posts/{id}/apply', [JobPostController::class, 'apply']);

Route::get('api/applied-jobs', [JobPostController::class, 'appliedJob']);

Route::delete('api/job-posts/{id}/unapply', [JobPostController::class, 'unapply']);

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth']);

Route::middleware('auth')->get('/api/client/job-posts', [JobPostController::class, 'getClientJobPosts']);

Route::middleware('auth')->get('/api/provider/dashboard-applications', [JobPostController::class, 'getProviderDashboardApplications']);

Route::post('api/job-posts/{id}/choose-provider', [JobPostController::class, 'chooseProvider'])->middleware('auth');

Route::get('api/job-posts/{id}/applications', [JobPostController::class, 'getJobApplications'])->middleware('auth');
