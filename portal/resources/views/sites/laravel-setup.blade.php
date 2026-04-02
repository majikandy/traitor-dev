@extends('layouts.app')

@section('title', 'Laravel Setup — ' . $site->name)
@section('page-title', 'Laravel Setup')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('sites.show', $site) }}" class="hover:text-gray-700">{{ $site->name }}</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">Laravel Setup</span>
</nav>
@endsection

@section('content')
<div class="max-w-lg space-y-5">

    @if(session('info'))
    <div class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
        {{ session('info') }}
    </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-600">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" /></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Database setup required</h2>
                <p class="text-sm text-gray-500">One-time setup for <span class="font-medium">{{ $site->name }}</span></p>
            </div>
        </div>

        <p class="text-sm text-gray-600 mb-5">
            This will create a dedicated MySQL database and user, write <code class="text-xs bg-gray-100 px-1 rounded">shared/.env</code>, then build your first release.
        </p>

        <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 mb-5 space-y-1 font-mono text-xs text-gray-600">
            <div><span class="text-gray-400">database</span>  traitor_{{ str_replace('-', '_', $site->slug) }}</div>
            <div><span class="text-gray-400">user     </span>  t_{{ str_replace('-', '_', $site->slug) }}</div>
            <div><span class="text-gray-400">password </span>  <span class="text-gray-400 italic">generated</span></div>
        </div>

        <form method="POST" action="{{ route('sites.laravel-setup.submit', $site) }}" id="setup-form">
            @csrf
            <div class="flex items-center gap-3">
                <button type="submit" id="setup-btn"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg id="setup-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    <span id="setup-label">Create database &amp; build first release</span>
                </button>
                <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:text-gray-700">Skip — I'll configure manually</a>
            </div>
        </form>
    </div>
</div>
<script>
    document.getElementById('setup-form').addEventListener('submit', function () {
        var btn = document.getElementById('setup-btn');
        btn.disabled = true;
        document.getElementById('setup-spinner').classList.remove('hidden');
        document.getElementById('setup-label').textContent = 'Setting up…';
    });
</script>
@endsection
