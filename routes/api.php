<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PlatController;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    /**
     *CRUD Categories.
     */
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::post('/categories/{category}/plats', [CategoryController::class, 'associatePlats']);
    Route::get('/categories/{category}/plats', [CategoryController::class, 'getPlatsByCategory']);
    /**
     * CRUD des Plats
     */
    Route::get('/plats', [PlatController::class, 'index']);
    Route::post('/plats', [PlatController::class, 'store']);
    Route::put('/plats/{plat}', [PlatController::class, 'update']);
    Route::get('/plats/{plat}', [PlatController::class, 'show']);
    Route::delete('/plats/{plat}', [PlatController::class, 'destroy']);
});