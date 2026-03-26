<?php

use App\Http\Controllers\PreviewController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\PasswordGate;
use App\Models\Site;
use Illuminate\Support\Facades\Route;

Route::get('/gate', fn() => view('gate'))->name('gate');
Route::post('/gate', function (\Illuminate\Http\Request $request) {
    if ($request->input('password') !== config('portal.password')) {
        return back()->withErrors(['password' => 'Wrong password.']);
    }
    $request->session()->put('portal_authed', true);
    return redirect('/');
})->name('gate.check');

Route::middleware(PasswordGate::class)->group(function () {

Route::get('/', function () {
    return view('welcome', [
        'total' => Site::count(),
        'withDomain' => Site::whereNotNull('domain')->count(),
        'withReleases' => Site::where('current_release', '>', 0)->count(),
    ]);
});

Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
Route::get('/sites/{site}', [SiteController::class, 'show'])->name('sites.show');
Route::post('/sites/{site}/release', [SiteController::class, 'createRelease'])->name('sites.release');
Route::get('/sites/{site}/download/draft', [SiteController::class, 'downloadDraft'])->name('sites.download.draft');
Route::get('/sites/{site}/download/{release}', [SiteController::class, 'downloadRelease'])->name('sites.download.release');
Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

Route::get('/preview/{token}/{path?}', PreviewController::class)->where('path', '.*')->name('preview');

}); // end PasswordGate
