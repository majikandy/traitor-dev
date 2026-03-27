@extends('layouts.app')

@section('title', ($businessName ?: 'Traitor.dev') . ' — Dashboard')
@section('page-title', $businessName ?: 'Dashboard')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $businessName ?: 'Dashboard' }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $sites->count() }} site{{ $sites->count() === 1 ? '' : 's' }}</p>
    </div>
    <a href="{{ route('sites.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition self-start">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        New Site
    </a>
</div>

@if($sites->isEmpty())
    <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 mb-4">
            <svg class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" /></svg>
        </div>
        <p class="text-gray-500 mb-4">No sites yet.</p>
        <a href="{{ route('sites.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
            Create your first site
        </a>
    </div>
@else
    <div class="space-y-3">
        @foreach($sites as $site)
            <a href="{{ route('sites.show', $site) }}" class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-500 group-hover:bg-brand-50 group-hover:text-brand-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ $site->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $site->domain ?: $site->slug }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($site->maintenance_mode)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Maintenance
                        </span>
                    @elseif($site->live_release)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Live
                        </span>
                    @elseif($site->current_release > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">
                            v{{ $site->current_release }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">
                            No releases
                        </span>
                    @endif
                    <svg class="h-5 w-5 text-gray-300 group-hover:text-brand-500 transition" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
