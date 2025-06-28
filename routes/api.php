<?php

use App\Http\Controllers\AdsController;
use App\Http\Controllers\CustomerCategoryController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StatController;
use App\Http\Controllers\TransactionController;
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
    // Auth Routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });

    // Customer Routes
    Route::prefix('categories')->controller(CustomerCategoryController::class)->group(function () {
        Route::get('/', 'index');
    });

    Route::prefix('orders')->controller(CustomerOrderController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}/cancel', 'cancel');
    });

    // Protected User Routes
    Route::middleware('middleware.auth')->group(function () {
        // Protected Customer Routes
        Route::middleware('middleware.customer')->group(function () {
            Route::prefix('carts')->controller(CartController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::delete('/{id}', 'destroy');
                Route::put('/', 'store');
            });

            Route::prefix('favorites')->controller(FavoriteController::class)->group(function () {
                Route::get('/', 'getFavorite');
                Route::post('/', 'addToFavorite');
                Route::delete('/{id}', 'deleteFromFavorite');
            });
        });

        Route::prefix('profile')->controller(UserProfileController::class)->group(function () {
            Route::get('/', 'show');
            Route::put('/', 'update');
        });

        Route::delete('delete-account', [UserProfileController::class, 'delete']);

        // Protected Admin Routes
        Route::prefix('admin')->middleware('middleware.admin')->group(function () {
            Route::prefix('categories')->controller(CategoryController::class)->group(function () {
                Route::post('/', 'store');
                Route::put('/{slug}', 'update');
                Route::delete('/{slug}', 'destroy');
            });

            Route::prefix('products')->controller(ProductController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/{category_slug}', 'showByCategory');
                Route::post('/{category_slug}', 'store');
                Route::put('/{category_slug}/{product_slug}', 'update');
                Route::delete('/{category_slug}/{product_slug}', 'destroy');
            });

            Route::prefix('offline-users')->controller(OfflineController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{offline_user_id}', 'show');
                Route::put('/{offline_user_id}', 'update');
                Route::delete('/{offline_user_id}', 'destroy');
            });

            Route::prefix('ads')->controller(AdsController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::delete('/{id}', 'destroy');
            });

            Route::prefix('orders')->controller(OrderController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/{id}', 'show');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });

            Route::prefix('transactions')->controller(TransactionController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{id}', 'show');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });

            Route::prefix('stats')->controller(StatController::class)->group(function () {
                Route::get('/expense-income', 'getExpenseIncome');
                Route::get('/overview', 'getOverview');
                Route::get('/product-sales-by-category', 'getProductSalesByCategory');
            });
        });
    });
});
