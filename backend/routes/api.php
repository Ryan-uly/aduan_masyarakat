<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {

        // 🔐 Auth
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        Route::middleware('auth:sanctum')->group(function () {

            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

            // 📢 Complaint
            Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
            Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
            Route::get('/complaints/{id}', [ComplaintController::class, 'show'])->name('complaints.show');
            Route::put('/complaints/{id}', [ComplaintController::class, 'update'])->name('complaints.update');
            Route::delete('/complaints/{id}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');

            Route::post('/complaints/{id}/images', [ComplaintController::class, 'uploadImage'])->name('complaints.images');
        });

    });