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
            @if($site->domain)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Live
                </span>
            @elseif($site->current_release > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                    {{ $site->releases->count() }} {{ Str::plural('release', $site->releases->count()) }}
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    No releases
                </span>
            @endif
        </div>
        <p class="mt-1 text-sm text-gray-500">
            @if($site->domain)
                <a href="https://{{ $site->domain }}" target="_blank" class="text-brand-600 hover:underline">{{ $site->domain }}</a>
            @else
                {{ $site->slug }}
            @endif
        </p>
    </div>
</div>

{{-- Create Release --}}
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm mb-6">
    <div class="flex items-center gap-3 mb-1">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50">
            <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
        </div>
        <h2 class="text-base font-semibold text-gray-900">Create Release</h2>
    </div>
    <p class="text-sm text-gray-500 mb-4 ml-12">Upload a .zip to create a new versioned release with a shareable preview link.</p>

    <form action="{{ route('sites.release', $site) }}" method="POST" enctype="multipart/form-data" class="ml-12">
        @csrf
        <div id="dropzone" class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center cursor-pointer hover:border-brand-400 hover:bg-brand-50/50 transition mb-4">
            <input type="file" name="zip" accept=".zip" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
            <p class="dropzone-text text-sm text-gray-500">Drop a .zip or <span class="font-semibold text-brand-600">browse</span></p>
            <p class="dropzone-filename mt-1 text-sm font-semibold text-brand-600" id="filename"></p>
        </div>
        <div class="flex gap-3">
            <input
                type="text"
                name="notes"
                placeholder="Release notes (optional)"
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none transition"
            >
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">Create Release</button>
        </div>
    </form>
</div>

{{-- Releases --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Releases</h2>
    </div>

    @if($site->releases->isEmpty())
        <div class="p-8 text-center">
            <p class="text-sm text-gray-500">No releases yet. Upload a .zip above to create the first one.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($site->releases->sortByDesc('version') as $release)
                <div class="flex items-center justify-between px-6 py-4 {{ $release->version === $site->current_release ? 'bg-brand-50/50' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-bold text-gray-900">v{{ $release->version }}</span>
                        @if($release->version === $site->current_release)
                            <span class="inline-flex items-center rounded-full bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-700">latest</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500 hidden sm:inline">{{ $release->notes ?: '—' }}</span>
                        <span class="text-xs text-gray-400 min-w-[7rem] text-right">{{ $release->created_at->diffForHumans() }}</span>
                        <a href="{{ $release->previewUrl() }}" target="_blank" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Preview</a>
                        <a href="{{ route('sites.download.release', [$site, $release]) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Download</a>
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
