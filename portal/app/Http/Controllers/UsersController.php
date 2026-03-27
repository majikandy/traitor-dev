<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\InviteUserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::where('organisation_id', Auth::user()->organisation_id)->latest()->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make(Str::random(32)),
            'has_password'    => false,
            'organisation_id' => Auth::user()->organisation_id,
        ]);

        $token = Password::createToken($user);
        $user->notify(new InviteUserNotification($token));

        return back()->with('success', "Account created for {$user->name}. Login details sent to {$user->email}.");
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', "Removed {$user->name}.");
    }
}
