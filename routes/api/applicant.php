<?php

use App\Http\Controllers\Applicant\JobApplicationController;
use Illuminate\Support\Facades\Route;

Route::post('/application/{job}', [JobApplicationController::class, 'store'])
    ->whereNumber('job')->middleware('throttle:5,1');
