<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\RewardController;

Route::get('/', [MainController::class, 'index'])->name('home');
Route::get('/remix/{song}', [MainController::class, 'remixBySong'])->name('remix');
Route::post('/process', [MainController::class, 'process'])->name('process');

Route::get('/feed', [MainController::class, 'feed'])->name('feed');
Route::get('/result', [MainController::class, 'result'])->name('result');
Route::get('/profile', [MainController::class, 'profile'])->name('profile');

Route::post('/auth/check', [AuthController::class, 'checkAuth'])->name('auth.check');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/daily-reward', [AuthController::class, 'getDailyReward'])->name('daily-reward');

Route::post('/send-audio', [MainController::class, 'sendAudio'])->name('send-audio');
