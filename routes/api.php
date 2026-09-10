<?php

use App\Http\Controllers\Auth\RegisterApplicantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', RegisterApplicantController::class)
    ->middleware('throttle:5,1')
    ->name('auth.register');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
