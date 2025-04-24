<?php

use App\Http\Controllers\HorizonDemoController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Horizon Demo Routes
Route::get('/horizon-demo', [HorizonDemoController::class, 'index'])->name('horizon.demo');
Route::post('/horizon-demo/dispatch', [HorizonDemoController::class, 'dispatchJobs'])->name('horizon.dispatch');

// Search page
Route::get('/search-page', function () {
    return view('search');
})->name('search.page');

// Search Routes
Route::prefix('search')->group(function () {
    Route::get('/', [SearchController::class, 'search'])->name('search');
    Route::get('/users', [SearchController::class, 'searchUsers'])->name('search.users');
    Route::get('/nodes', [SearchController::class, 'searchNodes'])->name('search.nodes');
});
