<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\PlatController;
use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {

    // client routes
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // categories (read)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/categories/{category}/plats', [CategoryController::class, 'getPlatsByCategory']);

    // plats (read)
    Route::get('/plats', [PlatController::class, 'index']);
    Route::get('/plats/{plat}', [PlatController::class, 'show']);

    // recommendations (read)
    Route::post('/recommendations/analyze/{plat}', [RecommendationController::class, 'analyze']);
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/recommendations/{plat}', [RecommendationController::class, 'show']);
});


Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // categories (write)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::post('/categories/{category}/plats', [CategoryController::class, 'associatePlats']);

    // plats (write)
    Route::post('/plats', [PlatController::class, 'store']);
    Route::put('/plats/{plat}', [PlatController::class, 'update']);
    Route::delete('/plats/{plat}', [PlatController::class, 'destroy']);

    //ingredient (write)
    Route::post('/ingredients', [IngredientController::class, 'store']);
    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update']);
    Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy']);
});
