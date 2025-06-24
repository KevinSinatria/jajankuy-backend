<?php

use App\Http\Controllers\CartController;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\UserProfileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function() {
    // Auth
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('middleware.auth');

    Route::prefix('carts')->group(function() {
        Route::get('/', [CartController::class, 'index'])->middleware('middleware.auth');
        Route::post('/', [CartController::class, 'store'])->middleware('middleware.auth');
        Route::delete('/{id}', [CartController::class, 'destroy'])->middleware('middleware.auth');
        Route::put('/', [CartController::class, 'store'])->middleware('middleware.auth');
    });

    Route::prefix('favorites')->group(function() {
        Route::get('/', [FavoriteController::class, 'getFavorite'])->middleware('middleware.auth');
        Route::post('/', [FavoriteController::class, 'addToFavorite'])->middleware('middleware.auth');
        Route::delete('/{id}', [FavoriteController::class, 'deteleFromFavorite'])->middleware('middleware.auth');
    });
    // User Profile
    Route::get('/profile', [UserProfileController::class, 'get'])->middleware('middleware.auth');
    Route::put('/profile', [UserProfileController::class, 'update'])->middleware('middleware.auth');
    Route::delete('/profile', [UserProfileController::class, 'delete'])->middleware('middleware.auth');

    // Admin Access
    Route::prefix('admin')->group(function() {
        Route::prefix('categories')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{slug}', [CategoryController::class, 'update']);
            Route::delete('/{slug}', [CategoryController::class, 'destroy']);
        });

        Route::prefix('products')->group(function () {
            Route::post('/{category_slug}', [ProductController::class, 'store']);
            Route::put('/{category_slug}/{product_slug}', [ProductController::class, 'update']);
            Route::delete('/{category_slug}/{product_slug}', [ProductController::class, 'destroy']);
        });
    });
});