@extends('layouts.app')

@section('title', 'New Site — Traitor.dev')
@section('page-title', 'New Site')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('sites.index') }}" class="hover:text-gray-700">Sites</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">New Site</span>
</nav>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">New Site</h1>
    <p class="mt-1 text-sm text-gray-500">Give it a name and optionally upload a zip of your site files.</p>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm max-w-xl">
    <form action="{{ route('sites.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Site name --}}
        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Site Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="My Awesome Site"
                required
                autofocus
                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none transition"
            >
            <p class="mt-1.5 text-xs text-gray-400">This becomes the slug for your preview URL.</p>
        </div>

        {{-- File upload --}}
        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Site Files (zip)</label>
            <div id="dropzone" class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center cursor-pointer hover:border-brand-400 hover:bg-brand-50/50 transition">
                <input type="file" id="zip" name="zip" accept=".zip" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                <p class="dropzone-text text-sm text-gray-500">Drag & drop a .zip file here, or <span class="font-semibold text-brand-600">click to browse</span></p>
                <p class="dropzone-filename mt-2 text-sm font-semibold text-brand-600" id="filename"></p>
            </div>
            <p class="mt-1.5 text-xs text-gray-400">Optional — you can upload later. Your zip should contain the files you want served (index.html, css/, etc).</p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Create Site
            </button>
            <a href="{{ route('sites.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
