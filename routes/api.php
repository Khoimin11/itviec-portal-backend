<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/api/auth.php';

Route::middleware(['auth:sanctum', 'applicant'])
    ->group(__DIR__.'/api/applicant.php');

Route::middleware(['auth:sanctum', 'company'])
    ->group(__DIR__.'/api/company.php');

// Load dynamic public routes after the specific company routes.
require __DIR__.'/api/public.php';
