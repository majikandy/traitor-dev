<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
        ]);

        Mail::raw(
            "Hi {$user->name},\n\nYou've been added to Traitor.dev.\n\nLogin: https://portal.traitor.dev\nEmail: {$user->email}\nPassword: {$password}\n\nChange your password after logging in.\n\nTraitor.dev",
            fn($m) => $m->to($user->email)->subject('Your Traitor.dev account')
        );

        return back()->with('success', "Account created for {$user->name}. Login details sent to {$user->email}.");
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', "Removed {$user->name}.");
    }
}
