<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Routes Web
|--------------------------------------------------------------------------
*/

// Page d'accueil protégée
Route::middleware('auth')->group(function () {
    Route::view('/', 'home_page');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Routes liées aux profils
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('{id}', [ProfileController::class, 'show'])->name('show');
        Route::put('{id}', [ProfileController::class, 'update'])->name('update');
        Route::post('{id}/upload-cv', [ProfileController::class, 'uploadCv']);
        Route::delete('{id}/delete-cv', [ProfileController::class, 'deleteCv']);
        Route::post('{id}/upload-profile-picture', [ProfileController::class, 'uploadProfilePicture']);
        Route::get('{id}/profile-picture', [ProfileController::class, 'profilePicture']);
        Route::get('{id}/cv', [ProfileController::class, 'showCv']);
        Route::get('{id}/skills', [ProfileController::class, 'skills']);
        Route::post('{id}/add-skill', [ProfileController::class, 'addSkill']);
        Route::delete('{id}/remove-skill', [ProfileController::class, 'removeSkill']);
    });

    // Routes pour Job Posts
    Route::prefix('api')->group(function () {
        Route::resource('job-posts', JobPostController::class)->except(['edit', 'show', 'create']);

        Route::get('applied-jobs', [JobPostController::class, 'appliedJob']);
        Route::get('client/job-posts', [JobPostController::class, 'clientJobPosts']);
        Route::get('job-posts/{id}/applications', [JobPostController::class, 'jobApplications'])
        ->name('job-posts.applications');
        Route::get('provider/dashboard-applications', [JobPostController::class, 'providerApplications']);
        Route::post('job-posts/{id}/choose-provider', [JobPostController::class, 'chooseProvider']);
        Route::post('applications/{jobPostId}/{providerId}/update-status', [JobPostController::class, 'updateApplicationStatus']);
        Route::post('job-posts/{id}/apply', [JobPostController::class, 'storeApply']);
        Route::delete('job-posts/{id}/unapply', [JobPostController::class, 'destroyApply']);
    });
    Route::middleware('auth')->get('/create-offer', [JobPostController::class, 'create'])->name('job-posts.create');
});

// Authentification publique
Route::get('/login', fn() => view('login'))->name('login');
Route::post('/logout', fn() => Auth::logout() && response()->json(['message' => 'Logout successful']))->name('logout');

// API d'authentification
Route::prefix('api')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('auth-check', fn() => response()->json(['authenticated' => Auth::check()]));
    Route::get('user', fn() => response()->json(Auth::user()))->middleware('auth');
});

// Gestion des compétences
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('skills', [SkillsController::class, 'index']);
});