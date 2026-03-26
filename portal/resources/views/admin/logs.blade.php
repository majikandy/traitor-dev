@extends('layouts.app')

@section('title', 'Logs — Traitor.dev')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <span class="text-gray-400">Admin</span>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">Server Logs</span>
</nav>
@endsection

@section('content')
<div class="space-y-3">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Last {{ count($entries) }} entries, newest first.</p>
        <button onclick="location.reload()" class="text-sm text-brand-600 hover:text-brand-700 font-medium transition">Refresh</button>
    </div>

    @if(empty($entries))
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400">
            Log file is empty.
        </div>
    @else
        @foreach($entries as $entry)
            @php
                $colours = match($entry['level']) {
                    'error'    => 'border-red-200 bg-red-50',
                    'warning'  => 'border-yellow-200 bg-yellow-50',
                    'critical' => 'border-red-300 bg-red-100',
                    'debug'    => 'border-gray-100 bg-gray-50',
                    default    => 'border-gray-200 bg-white',
                };
                $badge = match($entry['level']) {
                    'error'    => 'bg-red-100 text-red-700',
                    'warning'  => 'bg-yellow-100 text-yellow-700',
                    'critical' => 'bg-red-200 text-red-800',
                    'debug'    => 'bg-gray-100 text-gray-500',
                    default    => 'bg-blue-100 text-blue-700',
                };
                $detail = trim($entry['detail'] ?? '');
                // Strip the JSON context blob — just show the human message
                $message = preg_replace('/\s*\{.*$/s', '', $entry['message']);
            @endphp

            <div class="rounded-lg border {{ $colours }} p-3 text-sm font-mono">
                <div class="flex items-start gap-3">
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-xs font-semibold uppercase tracking-wide {{ $badge }}">
                        {{ $entry['level'] }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="break-words">{{ $message }}</p>
                        @if($detail)
                            <details class="mt-1">
                                <summary class="cursor-pointer text-xs text-gray-400 hover:text-gray-600">Stack trace</summary>
                                <pre class="mt-2 overflow-x-auto text-xs text-gray-600 whitespace-pre-wrap">{{ $detail }}</pre>
                            </details>
                        @endif
                    </div>
                    <span class="shrink-0 text-xs text-gray-400">{{ $entry['date'] }}</span>
                </div>
            </div>
        @endforeach
    @endif

</div>
@endsection
