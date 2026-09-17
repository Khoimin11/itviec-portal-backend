<?php

use App\Http\Controllers\PublicApi\CatalogController;
use App\Http\Controllers\PublicApi\CompanyController;
use App\Http\Controllers\PublicApi\EmployerController;
use App\Http\Controllers\PublicApi\JobController;
use Illuminate\Support\Facades\Route;

Route::get('/industry', [CatalogController::class, 'industries']);
Route::get('/skill', [CatalogController::class, 'skills']);
Route::get('/company/top-employers', [EmployerController::class, 'index']);
Route::get('/company/{slug}', [CompanyController::class, 'show'])->where('slug', '[a-z0-9-]+');
Route::get('/job/{job}', [JobController::class, 'show'])->whereNumber('job');
