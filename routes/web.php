<?php

use App\Http\Controllers\LearningController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Learning routes
    Route::get('/learning', [LearningController::class, 'index'])->name('index');
    Route::get('/learning/{slug}', [LearningController::class, 'show'])->name('show');
})->as('learning.');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/master.php';
