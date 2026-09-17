<?php

use App\Http\Controllers\Auth\ApplicantAuthController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\Auth\SessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('/register', [ApplicantAuthController::class, 'register'])->middleware('throttle:5,1')->name('register');
    Route::post('/login', [ApplicantAuthController::class, 'login'])->middleware('throttle:5,1')->name('login');
    Route::post('/register-company', [CompanyAuthController::class, 'register'])->middleware('throttle:5,1,company-register')->name('register-company');
    Route::post('/login-company', [CompanyAuthController::class, 'login'])->middleware('throttle:5,1,company-login')->name('login-company');
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/auth/logout', [SessionController::class, 'destroy'])->name('auth.logout');
    Route::get('/auth/account', [SessionController::class, 'show'])->name('auth.account');
});
