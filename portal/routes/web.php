<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GitHubController;
use App\Models\Organisation;
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

Route::get('/version', fn() => response(file_get_contents(base_path('VERSION')), 200, ['Content-Type' => 'text/plain']));

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/passkey-start', [AuthController::class, 'registerPasskeyStart'])->name('register.passkey-start');
Route::post('/register/passkey-cleanup', [AuthController::class, 'registerPasskeyCleanup'])->name('register.passkey-cleanup');
Route::get('/invite/{token}', [AuthController::class, 'acceptInvite'])->name('invite.accept');

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

// Setup auth method (passkey or password) — for accounts with neither yet.
// Inside auth but NOT inside auth.method to avoid redirect loops.
Route::middleware('auth')->group(function () {
    Route::get('/setup', [AuthController::class, 'showSetupAuth'])->name('setup-auth');
    Route::post('/setup/password', [AuthController::class, 'saveSetupPassword'])->name('setup-auth.save');
});

// Protected routes
Route::middleware(['auth', 'auth.method'])->group(function () {
    Route::get('/', function () {
        return view('welcome', [
            'businessName' => \App\Models\Setting::get('business_name'),
            'sites' => Site::orderBy('name')->get(),
        ]);
    });

    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::get('/sites/{site}', [SiteController::class, 'show'])->name('sites.show');
    Route::patch('/sites/{site}', [SiteController::class, 'update'])->name('sites.update');
    Route::post('/sites/{site}/release', [SiteController::class, 'createRelease'])->name('sites.release');
    Route::post('/sites/{site}/releases/{version}/promote', [SiteController::class, 'promoteRelease'])->name('sites.releases.promote');
    Route::post('/sites/{site}/releases/{version}/version-preview/share', [SiteController::class, 'shareVersionPreview'])->name('sites.releases.version-preview.share');
    Route::post('/sites/{site}/releases/{version}/version-preview/regenerate', [SiteController::class, 'regenerateVersionPreviewToken'])->name('sites.releases.version-preview.regenerate');
    Route::post('/sites/{site}/releases/{version}/version-preview/revoke', [SiteController::class, 'revokeVersionPreview'])->name('sites.releases.version-preview.revoke');
    Route::post('/sites/{site}/revert-to-coming-soon', [SiteController::class, 'revertToComingSoon'])->name('sites.revert-to-coming-soon');
    Route::post('/sites/{site}/domain', [SiteController::class, 'attachDomain'])->name('sites.domain.attach');
    Route::delete('/sites/{site}/domain', [SiteController::class, 'detachDomain'])->name('sites.domain.detach');
    Route::post('/sites/{site}/domain/check-dns', [SiteController::class, 'checkDns'])->name('sites.domain.check-dns');
    Route::post('/sites/{site}/domain/force-active', [SiteController::class, 'forceActiveDomain'])->name('sites.domain.force-active');
    Route::get('/sites/{site}/download/draft', [SiteController::class, 'downloadDraft'])->name('sites.download.draft');
    Route::get('/sites/{site}/download/{release}', [SiteController::class, 'downloadRelease'])->name('sites.download.release');
    Route::post('/sites/{site}/maintenance', [SiteController::class, 'toggleMaintenance'])->name('sites.maintenance.toggle');
    Route::get('/sites/{site}/laravel-setup', [SiteController::class, 'laravelSetupForm'])->name('sites.laravel-setup');
    Route::post('/sites/{site}/laravel-setup', [SiteController::class, 'laravelSetup'])->name('sites.laravel-setup.submit');
    Route::put('/sites/{site}/env', [SiteController::class, 'updateEnv'])->name('sites.env.update');
    Route::get('/sites/{site}/artisan', [SiteController::class, 'artisanCommands'])->name('sites.artisan.commands');
    Route::post('/sites/{site}/artisan', [SiteController::class, 'artisanRun'])->name('sites.artisan.run');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

    Route::get('/team', [UsersController::class, 'index'])->name('team.index');
    Route::post('/team', [UsersController::class, 'store'])->name('team.store');
    Route::post('/team/{user}/resend-invite', [UsersController::class, 'resendInvite'])->name('team.resend-invite');
    Route::delete('/team/{user}', [UsersController::class, 'destroy'])->name('team.destroy');

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs');
        Route::get('/admin/emails', [AdminController::class, 'emails'])->name('admin.emails');
        Route::get('/admin/emails/{email}', [AdminController::class, 'showEmail'])->name('admin.emails.show');
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::get('/admin/cpanel', [AdminController::class, 'cpanel'])->name('admin.cpanel');
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

Route::any('/preview/{token}/{path?}', PreviewController::class)->where('path', '.*')->name('preview');

Route::get('/version', fn() => response(trim(file_get_contents(base_path('VERSION'))), 200, ['Content-Type' => 'text/plain']));

// GitHub App — webhook is public (CSRF exempt via bootstrap/app.php)
Route::post('/github/webhook', [GitHubController::class, 'webhook'])->name('github.webhook');

Route::middleware(['auth', 'auth.method'])->group(function () {
    Route::get('/github/install/{site}', [GitHubController::class, 'install'])->name('github.install');
    Route::get('/github/{site}/manage-repos', [GitHubController::class, 'manageRepos'])->name('github.manage-repos');
    Route::get('/github/callback', [GitHubController::class, 'callback'])->name('github.callback');
    Route::get('/github/{site}/repo', [GitHubController::class, 'selectRepoForm'])->name('github.select-repo-form');
    Route::get('/github/{site}/repo/dirs', [GitHubController::class, 'repoDirs'])->name('github.repo-dirs');
    Route::get('/github/{site}/repo/branches', [GitHubController::class, 'repoBranches'])->name('github.repo-branches');
    Route::post('/github/{site}/repo', [GitHubController::class, 'selectRepo'])->name('github.select-repo');
    Route::post('/github/{site}/auto-deploy', [GitHubController::class, 'toggleAutoDeploy'])->name('github.auto-deploy');
    Route::delete('/github/{site}', [GitHubController::class, 'disconnect'])->name('github.disconnect');
});
