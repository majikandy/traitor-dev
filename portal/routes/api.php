<?php

use App\Http\Controllers\Api\SiteApiController;
use Illuminate\Support\Facades\Route;

Route::get('/sites', [SiteApiController::class, 'index']);
Route::post('/sites', [SiteApiController::class, 'store']);
Route::get('/sites/{site}', [SiteApiController::class, 'show']);
Route::post('/sites/{site}/upload', [SiteApiController::class, 'upload']);
Route::post('/sites/{site}/publish', [SiteApiController::class, 'publish']);
Route::post('/sites/{site}/rollback', [SiteApiController::class, 'rollback']);
Route::delete('/sites/{site}', [SiteApiController::class, 'destroy']);
