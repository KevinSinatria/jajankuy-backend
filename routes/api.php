<?php

use App\Http\Controllers\AdsController;
use App\Http\Controllers\CustomerCategoryController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\OrderController;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OfflineController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\UserProfileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::prefix('categories')->group(function () {
        Route::get('/', [CustomerCategoryController::class, 'index']);
    });

    Route::prefix('orders')->group(function () {
        Route::post('/', [CustomerOrderController::class, 'store']);
        Route::put('/{id}/cancel', [CustomerOrderController::class, 'update']);
    });

    Route::prefix('carts')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::put('/', [CartController::class, 'store']);
    })->middleware('auth:sanctum');

    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'getFavorite']);
        Route::post('/', [FavoriteController::class, 'addToFavorite']);
        Route::delete('/{id}', [FavoriteController::class, 'deteleFromFavorite']);
    })->middleware('auth:sanctum');

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'get']);
        Route::put('/', [UserProfileController::class, 'update']);
        Route::delete('/', [UserProfileController::class, 'delete']);
    })->middleware('auth:sanctum');

    // Admin Access
    Route::prefix('admin')->group(function () {
        // Categories Management
        Route::prefix('categories')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{slug}', [CategoryController::class, 'update']);
            Route::delete('/{slug}', [CategoryController::class, 'destroy']);
        });

        // Products Management
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{category_slug}', [ProductController::class, 'showByCategory']);
            Route::post('/{category_slug}', [ProductController::class, 'store']);
            Route::put('/{category_slug}/{product_slug}', [ProductController::class, 'update']);
            Route::delete('/{category_slug}/{product_slug}', [ProductController::class, 'destroy']);
        });

        // Offline Users Management  
        Route::prefix('offline-users')->group(function () {
            Route::get('/', [OfflineController::class, 'index']);
            Route::post('/', [OfflineController::class, 'store']);
            Route::get('/{offline_user_id}', [OfflineController::class, 'show']);
            Route::put('/{offline_user_id}', [OfflineController::class, 'update']);
            Route::delete('/{offline_user_id}', [OfflineController::class, 'destroy']);
        });

        // Ads Management
        Route::prefix('ads')->group(function () {
            Route::get('/', [AdsController::class, 'index']);
            Route::post('/', [AdsController::class, 'store']);
            Route::delete('/{id}', [AdsController::class, 'destroy']);
        });

        // Orders Management
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::put('/{id}', [OrderController::class, 'update']);
            Route::delete('/{id}', [OrderController::class, 'destroy']);
        });
    });
});