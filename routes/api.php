<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/api/public.php';
require __DIR__.'/api/auth.php';

Route::middleware(['auth:sanctum', 'applicant'])
    ->group(__DIR__.'/api/applicant.php');

Route::middleware(['auth:sanctum', 'company'])
    ->group(__DIR__.'/api/company.php');
