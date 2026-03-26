@extends('layouts.app')

@section('title', 'Users — Traitor.dev')
@section('page-title', 'Users')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">Users</span>
</nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="mt-1 text-sm text-gray-500">People with access to this portal.</p>
    </div>
</div>

{{-- Add user --}}
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm mb-6">
    <h2 class="text-base font-semibold text-gray-900 mb-4">Add user</h2>
    <form action="{{ route('users.store') }}" method="POST" class="flex gap-3">
        @csrf
        <input type="text" name="name" placeholder="Name" required value="{{ old('name') }}"
            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        <input type="email" name="email" placeholder="Email" required value="{{ old('email') }}"
            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
            Add &amp; send invite
        </button>
    </form>
</div>

{{-- User list --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="divide-y divide-gray-100">
        @foreach($users as $user)
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400">{{ $user->email }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                            onsubmit="return confirm('Remove {{ $user->name }}?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">Remove</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
