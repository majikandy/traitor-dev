@extends('layouts.app')

@section('title', 'Team — Traitor.dev')
@section('page-title', 'Team')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">Team</span>
</nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Team</h1>
        <p class="mt-1 text-sm text-gray-500">People with access to this portal.</p>
    </div>
</div>

{{-- Invite member --}}
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm mb-6">
    <h2 class="text-base font-semibold text-gray-900 mb-4">Invite member</h2>
    <form action="{{ route('team.store') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
        @csrf
        <input type="text" name="name" placeholder="Name" required value="{{ old('name') }}"
            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        <input type="email" name="email" placeholder="Email" required value="{{ old('email') }}"
            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition whitespace-nowrap">
            Send invite
        </button>
    </form>
</div>

{{-- Members list --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">
            Members &mdash; <span class="font-normal text-gray-500">{{ $businessName }} organisation</span>
        </h2>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($users as $user)
            <div class="flex items-center justify-between px-6 py-4 gap-4">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                        @if(!$user->signed_up_at)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 border border-amber-200">Invite pending</span>
                        @endif
                        @if($user->id === auth()->id())
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">You</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
                </div>

                <div class="flex items-center gap-4 flex-shrink-0">
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-medium text-gray-500">Invited</p>
                        <p class="text-xs text-gray-400" title="{{ $user->created_at->format('d M Y H:i') }}">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-medium text-gray-500">Joined</p>
                        @if($user->signed_up_at)
                            <p class="text-xs text-gray-400" title="{{ $user->signed_up_at->format('d M Y H:i') }}">{{ $user->signed_up_at->diffForHumans() }}</p>
                        @else
                            <p class="text-xs text-gray-300">—</p>
                        @endif
                    </div>
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-medium text-gray-500">Last seen</p>
                        @if($user->last_login_at)
                            <p class="text-xs text-gray-400" title="{{ $user->last_login_at->format('d M Y H:i') }}">{{ $user->last_login_at->diffForHumans() }}</p>
                        @else
                            <p class="text-xs text-gray-300">—</p>
                        @endif
                    </div>

                    @if($user->id !== auth()->id())
                        <div class="flex items-center gap-2">
                            @if(!$user->signed_up_at)
                                {{-- Resend invite --}}
                                <form action="{{ route('team.resend-invite', $user) }}" method="POST">
                                    @csrf
                                    <button class="rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50 transition">
                                        Resend
                                    </button>
                                </form>
                                {{-- Cancel invite --}}
                                <form action="{{ route('team.destroy', $user) }}" method="POST"
                                    data-confirm="Cancel invite for {{ $user->email }}? This will delete the pending account.">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                        Cancel invite
                                    </button>
                                </form>
                            @else
                                {{-- Remove active member --}}
                                <form action="{{ route('team.destroy', $user) }}" method="POST"
                                    data-confirm="Remove {{ $user->name }} from the team?">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="w-[90px] hidden sm:block"></div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
