<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function() {
    // Auth
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('login.auth');

    // Admin Access
    Route::prefix('admin')->group(function() {
        Route::prefix('categories')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{slug}', [CategoryController::class, 'update']);
            Route::delete('/{slug}', [CategoryController::class, 'destroy']);
        });
    });
});