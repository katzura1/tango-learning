<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Usercontroller;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // User routes
    Route::prefix('users')->as('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');   // halaman, bisa pakai inertia/blade
        Route::get('/list', [UserController::class, 'list'])->name('list'); // untuk data (AJAX)
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Categories routes
    Route::prefix('categories')->as('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/list', [CategoryController::class, 'list'])->name('list');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });
});
