@extends('layouts.app')

@section('title', 'cPanel — Traitor.dev')
@section('page-title', 'cPanel')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">cPanel</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Disk Usage --}}
    @if($diskUsage)
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Disk usage</h2>
        @php
            $used  = ($diskUsage['megabytes_used'] ?? 0);
            $limit = ($diskUsage['megabytes_limit'] ?? 0);
            $pct   = $limit > 0 ? min(100, round($used / $limit * 100)) : 0;
        @endphp
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">{{ number_format($used) }} MB used</span>
                    <span class="text-gray-400">{{ $limit > 0 ? number_format($limit) . ' MB limit' : 'Unlimited' }}</span>
                </div>
                @if($limit > 0)
                <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-2 rounded-full {{ $pct > 80 ? 'bg-red-500' : ($pct > 60 ? 'bg-amber-400' : 'bg-emerald-500') }}" style="width: {{ $pct }}%"></div>
                </div>
                @endif
            </div>
            @if($limit > 0)
            <span class="text-sm font-semibold {{ $pct > 80 ? 'text-red-600' : 'text-gray-500' }}">{{ $pct }}%</span>
            @endif
        </div>
    </div>
    @endif

    {{-- Preview Subdomains --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Preview subdomains</h2>
            <p class="text-sm text-gray-500 mt-0.5">Subdomains under {{ config('services.cpanel.preview_domain') }}</p>
        </div>
        @php
            $previewSubs = collect($subdomains)->filter(fn($s) => str_contains($s['domain'] ?? '', '.' . config('services.cpanel.preview_domain')))->values();
        @endphp
        @if($previewSubs->isEmpty())
            <p class="px-6 py-4 text-sm text-gray-400">No preview subdomains found.</p>
        @else
            <ul class="divide-y divide-gray-50">
                @foreach($previewSubs as $sub)
                <li class="flex items-center justify-between px-6 py-3">
                    <span class="font-mono text-sm text-gray-800">{{ $sub['domain'] }}</span>
                    <span class="text-xs text-gray-400 truncate max-w-xs hidden sm:block">{{ $sub['dir'] ?? '' }}</span>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Addon Domains --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Addon domains</h2>
            <p class="text-sm text-gray-500 mt-0.5">Customer domains attached in cPanel</p>
        </div>
        @if(empty($addonDomains))
            <p class="px-6 py-4 text-sm text-gray-400">No addon domains found.</p>
        @else
            <ul class="divide-y divide-gray-50">
                @foreach($addonDomains as $domain)
                <li class="flex items-center justify-between px-6 py-3">
                    <span class="font-mono text-sm text-gray-800">{{ $domain['domain'] ?? $domain }}</span>
                    <span class="text-xs text-gray-400 truncate max-w-xs hidden sm:block">{{ $domain['dir'] ?? '' }}</span>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- SSL Certificates --}}
    @if(!empty($sslCerts))
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">SSL certificates</h2>
        </div>
        <ul class="divide-y divide-gray-50">
            @foreach($sslCerts as $cert)
            @php
                $expiry    = isset($cert['not_after']) ? \Carbon\Carbon::createFromTimestamp($cert['not_after']) : null;
                $isExpired = $expiry && $expiry->isPast();
                $isSoon    = $expiry && !$isExpired && $expiry->diffInDays() < 30;
            @endphp
            <li class="flex items-center justify-between px-6 py-3 gap-4">
                <span class="font-mono text-sm text-gray-800 truncate">{{ implode(', ', (array)($cert['domains'] ?? [$cert['subject']['commonName'] ?? '?'])) }}</span>
                @if($expiry)
                    <span class="text-xs flex-shrink-0 {{ $isExpired ? 'text-red-600 font-semibold' : ($isSoon ? 'text-amber-600' : 'text-gray-400') }}">
                        {{ $isExpired ? 'Expired' : 'Expires' }} {{ $expiry->diffForHumans() }}
                    </span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>
@endsection
