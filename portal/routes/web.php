<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasskeyController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Site;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset
Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    $status = Password::sendResetLink($request->only('email'));
    return $status === Password::ResetLinkSent
        ? back()->with('status', 'Reset link sent — check your email.')
        : back()->withErrors(['email' => __($status)]);
})->name('password.email');

Route::get('/reset-password/{token}', function (string $token, Request $request) {
    return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
})->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);
    $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
        $user->forceFill(['password' => Hash::make($password), 'has_password' => true])->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));
    });
    return $status === Password::PasswordReset
        ? redirect()->route('login')->with('status', 'Password reset successfully.')
        : back()->withErrors(['email' => __($status)]);
})->name('password.update');

// Protected routes
Route::middleware('auth')->group(function () {
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
    Route::patch('/sites/{site}', [SiteController::class, 'update'])->name('sites.update');
    Route::post('/sites/{site}/release', [SiteController::class, 'createRelease'])->name('sites.release');
    Route::get('/sites/{site}/download/draft', [SiteController::class, 'downloadDraft'])->name('sites.download.draft');
    Route::get('/sites/{site}/download/{release}', [SiteController::class, 'downloadRelease'])->name('sites.download.release');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs');
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    });

    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    Route::get('/passkeys/register-options', [PasskeyController::class, 'registerOptions'])->name('passkeys.register-options');
    Route::post('/passkeys/register', [PasskeyController::class, 'register'])->name('passkeys.register');
    Route::delete('/passkeys/{passkey}', [PasskeyController::class, 'destroy'])->name('passkeys.destroy');
});

Route::get('/passkeys/auth-options', [PasskeyController::class, 'authOptions'])->name('passkeys.auth-options');
Route::post('/passkeys/authenticate', [PasskeyController::class, 'authenticate'])->name('passkeys.authenticate');
Route::post('/passkeys/invite-options', [PasskeyController::class, 'invitePasskeyOptions'])->name('passkeys.invite-options');

Route::get('/preview/{token}/{path?}', PreviewController::class)->where('path', '.*')->name('preview');
