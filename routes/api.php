<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Public routes - anyone can view reviews
Route::get('/movies/search', [MovieController::class, 'search']);
Route::get('/movies/{type}', [MovieController::class, 'index']);
Route::get('/movie/{id}', [MovieController::class, 'show']);
Route::get('/reviews/movie/{movieId}', [ReviewController::class, 'getByMovie']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [UserController::class, 'index']);
    Route::post('/logout', [UserController::class, 'destroy']);

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);

    Route::get('/reviews/export/{type}', [ReviewController::class, 'export']);

    Route::get('/movies/reviewed', [MovieController::class, 'reviewed']);
    Route::get('/movies/reviewed/export/{type}', [MovieController::class, 'reviewedExport']);
});
