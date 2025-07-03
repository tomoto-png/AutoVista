<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\SearchController;

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/top', [TopController::class, 'index'])->name('top.index');
Route::get('/search', [TopController::class, 'index'])->name('search');
Route::get('/suggestions', [SearchController::class, 'suggestions']);

Route::middleware(['auth'])->group(function () {
    Route::post('/top', [TopController::class, 'store'])->name('top.store');
    Route::post('/like', [LikeController::class, 'Like'])->name('like.toggle');
    Route::get('/tags/search', [SearchController::class, 'search']);
    Route::prefix("mypage")->group(function () {
        Route::get('/', [MypageController::class, 'index'])->name('mypage.index');
        Route::post('/like', [LikeController::class, 'Like'])->name('like.toggle');
        Route::get('/edit', [MypageController::class, 'edit'])->name('mypage.edit');
        Route::put('/', [MypageController::class, 'update'])->name('mypage.update');
        Route::delete('/{id}', [MypageController::class, 'destroy'])->name('mypage.destroy');
        Route::get('/gallery/edit/{id}', [MypageController::class, 'editGallery'])->name('gallery.edit');
        Route::put('/gallery/{id}', [MypageController::class, 'updateGgallery'])->name('gallery.update');
    });
});
