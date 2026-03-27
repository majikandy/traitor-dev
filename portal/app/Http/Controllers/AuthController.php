<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'organisation' => 'required|string|max:255',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        $org = Organisation::create(['name' => $request->organisation]);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'has_password'    => true,
            'organisation_id' => $org->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $user->recordLogin();

        return redirect('/');
    }

    /**
     * Step 1 of passkey registration: create the org + user, log them in,
     * and return WebAuthn creation options. The client then calls
     * POST /passkeys/register to complete the ceremony.
     */
    public function registerPasskeyStart(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'organisation' => 'required|string|max:255',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
        ]);

        $org = Organisation::create(['name' => $request->organisation]);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make(Str::random(32)),
            'has_password'    => false,
            'organisation_id' => $org->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Delegate to PasskeyController to build creation options
        return app(PasskeyController::class)->registerOptions($request);
    }

    /**
     * Clean up an incomplete passkey registration (account created but passkey
     * ceremony failed or was cancelled). Deletes the org and user, logs out.
     */
    public function registerPasskeyCleanup(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            $orgId = $user->organisation_id;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            User::where('id', $user->id)->delete();
            Organisation::where('id', $orgId)->whereDoesntHave('users')->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        Auth::user()->recordLogin();

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function acceptInvite(string $token)
    {
        $user = User::where('invite_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'This invite link is invalid or has been cancelled.']);
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('setup-auth');
    }

    public function showSetupAuth()
    {
        return view('auth.setup-auth');
    }

    public function saveSetupPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();
        $user->update(['password' => Hash::make($request->password), 'has_password' => true]);
        $user->recordLogin();

        return redirect('/')->with('success', 'Password set. You are all set!');
    }

    public function showProfile()
    {
        return view('auth.profile');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update($request->only('name', 'email'));

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if ($user->has_password) {
            $request->validate([
                'current_password' => 'required',
                'password'         => ['required', 'confirmed', Password::min(8)],
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        } else {
            $request->validate([
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);
        }

        $user->update(['password' => Hash::make($request->password), 'has_password' => true]);

        return back()->with('success', 'Password set.');
    }
}
