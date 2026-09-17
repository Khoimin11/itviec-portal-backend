<?php

use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\Company\JobPostingController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('company')->group(function (): void {
    Route::put('/password', [CompanyAuthController::class, 'changePassword'])->middleware('throttle:5,1');
    Route::get('/all-job', [JobPostingController::class, 'index']);
    Route::get('/profile', [CompanyProfileController::class, 'show']);
    Route::put('/profile', [CompanyProfileController::class, 'update']);
});

Route::post('/job', [JobPostingController::class, 'store'])->middleware('throttle:10,1');
