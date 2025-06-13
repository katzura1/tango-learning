<?php

use App\Http\Controllers\LearningController;
use App\Http\Controllers\UserVocabularyController;
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

    // User Vocabulary routes
    Route::get('/favorites', [UserVocabularyController::class, 'favorites'])->name('favorites.index');
    Route::post('/vocabulary/{vocabulary}/favorite', [UserVocabularyController::class, 'toggleFavorite'])->name('vocabulary.favorite.toggle');
    Route::post('/vocabulary/{vocabulary}/status', [UserVocabularyController::class, 'updateStatus'])->name('vocabulary.status.update');

    Route::get('data-dashboard', [UserVocabularyController::class, 'dataDashboard'])->name('data.dashboard');
})->as('learning.');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/master.php';
