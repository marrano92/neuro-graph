<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\ContentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

// Node Routes
Route::prefix('nodes')->group(function () {
    Route::get('/', [NodeController::class, 'index']);
    Route::get('/{node}', [NodeController::class, 'show']);
    Route::get('/{node}/similar', [NodeController::class, 'findSimilar']);
    
    // Protected routes
    Route::middleware('auth:api')->group(function () {
        Route::post('/', [NodeController::class, 'store']);
        Route::put('/{node}', [NodeController::class, 'update']);
        Route::delete('/{node}', [NodeController::class, 'destroy']);
    });
});

// Content routes
Route::get('/contents', [ContentController::class, 'index']);
Route::get('/contents/{content}', [ContentController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/contents', [ContentController::class, 'store']);
    Route::put('/contents/{content}', [ContentController::class, 'update']);
    Route::delete('/contents/{content}', [ContentController::class, 'destroy']);
    Route::post('/contents/{content}/nodes', [ContentController::class, 'addNodes']);
    Route::delete('/contents/{content}/nodes', [ContentController::class, 'removeNodes']);
}); 