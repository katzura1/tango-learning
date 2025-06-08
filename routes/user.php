<?php

use App\Http\Controllers\Usercontroller;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/users', [Usercontroller::class, 'index'])->name('users.index');    // halaman, bisa pakai inertia/blade
    Route::get('/users/list', [UserController::class, 'list'])->name('users.list'); // untuk data (AJAX)
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});
