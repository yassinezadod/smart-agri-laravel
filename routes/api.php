<?php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\YieldComparisonController;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
  Route::get('/verify-email/{id}', [AuthController::class, 'verify'])->name('verification.verify');
  Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/yield/sync-data', [YieldComparisonController::class, 'getAllDataForSync']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/users/me', [AuthController::class, 'updateProfile']);
    Route::delete('/users/me', [AuthController::class, 'deleteProfile']);
    Route::post('/predict-yield', [PredictionController::class, 'predict']);
    Route::get('/predictions', [PredictionController::class, 'index']);
    Route::post('/yield/compare', [YieldComparisonController::class, 'compare']);
    Route::get('/yield/comparisons', [YieldComparisonController::class, 'index']);

    // Routes réservées aux ADMINS uniquement
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/admin/users', [AdminController::class, 'index']);
        // Tu pourras ajouter ici : supprimer, stats, etc.
    });


});
