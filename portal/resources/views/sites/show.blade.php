@extends('layouts.app')

@section('title', $site->name . ' — Traitor.dev')
@section('page-title', $site->name)

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('sites.index') }}" class="hover:text-gray-700">Sites</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">{{ $site->name }}</span>
</nav>
@endsection

@section('content')
{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $site->name }}</h1>
            @if($site->status === 'live')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Live
                </span>
            @elseif($site->status === 'draft')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Draft
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Paused
                </span>
            @endif
        </div>
        <p class="mt-1 text-sm text-gray-500">
            @if($site->domain)
                <a href="https://{{ $site->domain }}" target="_blank" class="text-brand-600 hover:underline">{{ $site->domain }}</a>
            @else
                {{ $site->slug }}.sites.traitor.dev
            @endif
        </p>
    </div>
    <a href="https://{{ $site->previewUrl() }}" target="_blank" class="inline-flex items-center gap-2 self-start rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
        Preview
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Upload --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-1">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50">
                <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Upload Files</h2>
        </div>
        <p class="text-sm text-gray-500 mb-4 ml-12">Upload a .zip to replace the current draft.</p>

        <form action="{{ route('sites.upload', $site) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="dropzone" class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center cursor-pointer hover:border-brand-400 hover:bg-brand-50/50 transition mb-4">
                <input type="file" name="zip" accept=".zip" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                <p class="dropzone-text text-sm text-gray-500">Drop a .zip or <span class="font-semibold text-brand-600">browse</span></p>
                <p class="dropzone-filename mt-1 text-sm font-semibold text-brand-600" id="filename"></p>
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">Upload</button>
        </form>
    </div>

    {{-- Publish --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-1">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Publish</h2>
        </div>
        <p class="text-sm text-gray-500 mb-4 ml-12">Snapshot the current draft and make it live.</p>

        <form action="{{ route('sites.publish', $site) }}" method="POST" class="flex gap-3">
            @csrf
            <input
                type="text"
                name="notes"
                placeholder="Release notes (optional)"
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none transition"
            >
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">Publish</button>
        </form>
    </div>
</div>

{{-- Releases --}}
<div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Releases</h2>
    </div>

    @if($site->releases->isEmpty())
        <div class="p-8 text-center">
            <p class="text-sm text-gray-500">No releases yet. Upload files and publish to create the first one.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($site->releases->sortByDesc('version') as $release)
                <div class="flex items-center justify-between px-6 py-4 {{ $release->version === $site->current_release ? 'bg-emerald-50/50' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-bold text-gray-900">v{{ $release->version }}</span>
                        @if($release->version === $site->current_release)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">current</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-6">
                        <span class="text-sm text-gray-500 hidden sm:inline">{{ $release->notes ?: '—' }}</span>
                        <span class="text-xs text-gray-400 min-w-[7rem] text-right">{{ $release->created_at->diffForHumans() }}</span>
                        @if($release->version !== $site->current_release)
                            <form action="{{ route('sites.rollback', $site) }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Rollback</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Danger Zone --}}
<div class="mt-6 rounded-xl border border-red-200 bg-white p-6 shadow-sm">
    <div class="flex items-center gap-3 mb-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50">
            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
        </div>
        <h2 class="text-base font-semibold text-gray-900">Danger Zone</h2>
    </div>
    <p class="text-sm text-gray-500 mb-4 ml-12">Permanently delete this site and all its releases.</p>
    <form action="{{ route('sites.destroy', $site) }}" method="POST" class="ml-12" onsubmit="return confirm('Delete {{ $site->name }}? This cannot be undone.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">Delete Site</button>
    </form>
</div>
@endsection
