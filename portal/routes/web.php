<?php

use App\Http\Controllers\SiteController;
use App\Models\Site;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'total' => Site::count(),
        'live' => Site::where('status', 'live')->count(),
        'drafts' => Site::where('status', 'draft')->count(),
    ]);
});

Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
Route::get('/sites/{site}', [SiteController::class, 'show'])->name('sites.show');
Route::post('/sites/{site}/upload', [SiteController::class, 'upload'])->name('sites.upload');
Route::post('/sites/{site}/publish', [SiteController::class, 'publish'])->name('sites.publish');
Route::post('/sites/{site}/rollback', [SiteController::class, 'rollback'])->name('sites.rollback');
Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
