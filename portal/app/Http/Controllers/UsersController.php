<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\InviteUserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::where('organisation_id', Auth::user()->organisation_id)->latest()->get();
        $businessName = Setting::get('business_name') ?? 'Traitor.dev';

        return view('team.index', compact('users', 'businessName'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $token = Str::random(40);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make(Str::random(32)),
            'has_password'    => false,
            'organisation_id' => Auth::user()->organisation_id,
            'invite_token'    => $token,
        ]);

        $user->notify(new InviteUserNotification($token));

        return back()->with('success', "Invite sent to {$user->email}.");
    }

    public function resendInvite(User $user)
    {
        abort_if($user->organisation_id !== Auth::user()->organisation_id, 403);
        abort_if($user->signed_up_at !== null, 422, 'User has already accepted their invite.');

        $token = Str::random(40);
        $user->update(['invite_token' => $token]);
        $user->notify(new InviteUserNotification($token));

        return back()->with('success', "Invite resent to {$user->email}.");
    }

    public function destroy(User $user)
    {
        abort_if($user->organisation_id !== Auth::user()->organisation_id, 403);
        abort_if($user->id === Auth::id(), 422, 'You cannot remove yourself.');

        $label = $user->signed_up_at ? "Removed {$user->name}." : "Invite cancelled for {$user->email}.";
        $user->delete();

        return back()->with('success', $label);
    }
}
