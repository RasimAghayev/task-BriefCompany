<?php

use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| The catalogue is read by everyone, but only an authenticated caller may
| change it. The writes ask for a token of Sanctum in the header
| "Authorization: Bearer <token>".
|
| A token is issued by the command "php artisan api:token <email>".
|
*/

Route::apiResource('category', CategoryController::class)
    ->except(['store', 'update', 'destroy']);

Route::apiResource('product', ProductController::class)
    ->except(['store', 'update', 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('category', CategoryController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('product', ProductController::class)
        ->only(['store', 'update', 'destroy']);
});
