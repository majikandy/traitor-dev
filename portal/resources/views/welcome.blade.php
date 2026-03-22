@extends('layouts.app')

@section('title', 'Traitor.dev — Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-1 text-sm text-gray-500">Overview of your sites.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
    <a href="{{ route('sites.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50">
                <svg class="h-6 w-6 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $total }}</p>
                <p class="text-sm text-gray-500 group-hover:text-brand-600 transition">Total Sites</p>
            </div>
        </div>
    </a>

    <a href="{{ route('sites.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-emerald-300 hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $withDomain }}</p>
                <p class="text-sm text-gray-500 group-hover:text-emerald-600 transition">With Domain</p>
            </div>
        </div>
    </a>

    <a href="{{ route('sites.index') }}" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50">
                <svg class="h-6 w-6 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $withReleases }}</p>
                <p class="text-sm text-gray-500 group-hover:text-brand-600 transition">With Releases</p>
            </div>
        </div>
    </a>
</div>

{{-- Quick actions --}}
<div class="flex flex-wrap gap-3">
    <a href="{{ route('sites.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Create New Site
    </a>
    <a href="{{ route('sites.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
        View All Sites
    </a>
</div>
@endsection
