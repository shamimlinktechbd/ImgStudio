<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ImageController::class, 'index'])->name('images.index');
Route::get('/category/{category}', [ImageController::class, 'index'])->name('images.category');
Route::post('/images', [ImageController::class, 'store'])->name('images.store');
Route::get('/images/{image}', [ImageController::class, 'show'])->name('images.show');
Route::post('/images/{image}/process', [ImageController::class, 'process'])->name('images.process');
Route::get('/images/{image}/download', [ImageController::class, 'download'])->name('images.download');
Route::delete('/images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
Route::get('/history', [ImageController::class, 'history'])->name('images.history');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/images', [AdminController::class, 'images'])->name('admin.images');
    Route::get('/backgrounds', [AdminController::class, 'backgrounds'])->name('admin.backgrounds');
    Route::post('/backgrounds', [AdminController::class, 'storeBackground'])->name('admin.backgrounds.store');
    Route::delete('/backgrounds/{background}', [AdminController::class, 'destroyBackground'])->name('admin.backgrounds.destroy');
});
