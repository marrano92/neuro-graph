<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HorizonDemoController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Magic Link Authentication Routes (public)
Route::get('/login/magic', [MagicLinkController::class, 'showLoginForm'])->name('login.magic');
Route::post('/login/magic', [MagicLinkController::class, 'sendMagicLink'])->name('login.magic.send');
Route::get('/login/magic/{token}', [MagicLinkController::class, 'login'])->name('login.magic.login');

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');
    
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
    
    // Cytoscape.js Graph Demo
    Route::get('/graph-demo', function () {
        return view('graph-demo');
    })->name('graph.demo');
});
