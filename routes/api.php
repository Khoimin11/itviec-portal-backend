<?php

use App\Http\Controllers\Auth\ApplicantSessionController;
use App\Http\Controllers\Auth\LoginCompanyController;
use App\Http\Controllers\Auth\RegisterApplicantController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\CompanyCatalogController;
use App\Http\Controllers\CompanyProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', RegisterApplicantController::class)
    ->middleware('throttle:5,1')
    ->name('auth.register');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [ApplicantSessionController::class, 'store'])
    ->middleware('throttle:5,1')->name('auth.login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', [ApplicantSessionController::class, 'destroy'])->name('auth.logout');
    Route::get('/auth/account', [ApplicantSessionController::class, 'show'])->name('auth.account');
});

Route::post('/auth/register-company', RegisterCompanyController::class)
    ->middleware('throttle:5,1,company-register')->name('auth.register-company');

Route::post('/auth/login-company', LoginCompanyController::class)
    ->middleware('throttle:5,1,company-login')->name('auth.login-company');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/company/profile', [CompanyProfileController::class, 'show']);
});

Route::get('/industry', [CompanyCatalogController::class, 'industries']);
Route::get('/skill', [CompanyCatalogController::class, 'skills']);
