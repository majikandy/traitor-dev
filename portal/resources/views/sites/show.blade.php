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

@if($site->maintenance_mode)
<style>
/* Subtle hazard wash across the entire page content area */
main { background-image: repeating-linear-gradient(-45deg, rgba(245,158,11,0.04) 0, rgba(245,158,11,0.04) 20px, transparent 20px, transparent 40px) !important; }
</style>
@endif
@section('content')
@if($site->maintenance_mode)
{{-- Maintenance mode hazard banner --}}
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-4 sm:-mt-6 lg:-mt-8 mb-8">
    <div style="background:repeating-linear-gradient(-45deg,#f59e0b 0,#f59e0b 18px,#1c1917 18px,#1c1917 36px);height:12px;"></div>
    <div style="background:#1c1917;" class="px-6 py-4 flex items-center gap-4">
        <span class="text-3xl leading-none flex-shrink-0">🚧</span>
        <div class="flex-1 min-w-0">
            <p class="font-black uppercase tracking-widest text-sm" style="color:#fbbf24;">Maintenance mode active</p>
            <p class="text-xs mt-0.5" style="color:rgba(253,230,138,0.55);">Visitors are seeing the coming soon page — use the toggle to bring the site back online</p>
        </div>
        <span class="text-3xl leading-none flex-shrink-0">🚧</span>
    </div>
    <div style="background:repeating-linear-gradient(-45deg,#f59e0b 0,#f59e0b 18px,#1c1917 18px,#1c1917 36px);height:12px;"></div>
</div>
@endif
{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Inline name editor --}}
            <form id="rename-form" method="POST" action="{{ route('sites.update', $site) }}" class="hidden items-center gap-2">
                @csrf
                @method('PATCH')
                <input id="rename-input" type="text" name="name" value="{{ $site->name }}"
                    class="text-2xl font-bold text-gray-900 border-b-2 border-brand-500 bg-transparent outline-none w-64"
                    onkeydown="if(event.key==='Escape') cancelRename()">
                <button type="submit" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Save</button>
                <button type="button" onclick="cancelRename()" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
            </form>
            <h1 id="site-name-display" class="text-2xl font-bold text-gray-900 cursor-pointer hover:text-brand-600 transition group flex items-center gap-2" onclick="startRename()" title="Click to rename">
                {{ $site->name }}
                <svg class="h-4 w-4 text-gray-300 group-hover:text-brand-400 transition" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
            </h1>
            @if($site->maintenance_mode)
                <span id="site-header-badge" class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Maintenance
                </span>
            @elseif($site->live_release)
                <span id="site-header-badge" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span id="live-status-badge">Release {{ $site->live_release }} live</span>
                </span>
            @elseif($site->current_release > 0)
                <span id="site-header-badge" class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Not published
                </span>
            @else
                <span id="site-header-badge" class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    No releases
                </span>
            @endif
        </div>
        <p class="mt-1 text-sm text-gray-500 flex items-center gap-2">
            @if($site->domain)
                <a href="https://{{ $site->domain }}" target="_blank" class="text-brand-600 hover:underline">{{ $site->domain }}</a>
                <span class="text-gray-300">·</span>
            @endif
            <a href="{{ $site->stagingUrl() }}" target="_blank" class="text-gray-400 hover:text-gray-600 font-mono text-xs hover:underline" title="Live staging URL — mirrors exactly what the real domain serves">{{ $site->slug }}.{{ config('services.cpanel.staging_domain') }}</a>
        </p>
    </div>
    <form method="POST" action="{{ route('sites.maintenance.toggle', $site) }}" class="flex-shrink-0" id="maintenance-toggle-form">
        @csrf
        <input type="hidden" name="maintenance_page" id="maintenance-page-input" value="brb">
        <input type="hidden" name="launch_date" id="launch-date-input" value="">
        <label class="flex items-center gap-2 cursor-pointer select-none group" title="{{ $site->maintenance_mode ? 'Bring site back online' : 'Enable maintenance mode' }}">
            <span class="text-xs font-medium text-gray-400 group-hover:text-gray-600 transition">Maintenance</span>
            <div class="relative">
                <input type="checkbox" class="sr-only" {{ $site->maintenance_mode ? 'checked' : '' }}
                    onchange="
                        var form = document.getElementById('maintenance-toggle-form');
                        if (this.checked) {
                            this.checked = false;
                            showMaintenanceOptions(function(page, date) {
                                document.getElementById('maintenance-page-input').value = page;
                                document.getElementById('launch-date-input').value = date || '';
                                form.submit();
                            });
                        } else {
                            form.submit();
                        }
                    ">
                <div class="w-10 h-6 rounded-full transition-colors duration-200 {{ $site->maintenance_mode ? 'bg-amber-400' : 'bg-gray-200' }}"></div>
                <div class="absolute top-1 left-1 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200 {{ $site->maintenance_mode ? 'translate-x-4' : '' }}"></div>
            </div>
        </label>
    </form>

    {{-- Maintenance options modal --}}
    <div id="maintenance-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="maintenance-backdrop"></div>
        <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Enable maintenance mode</h3>
            <div class="flex flex-col gap-3 mb-5">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                    <input type="radio" name="mpage" value="brb" checked class="mt-0.5 accent-brand-600">
                    <div>
                        <div class="text-sm font-medium text-gray-900">Be Right Back</div>
                        <div class="text-xs text-gray-500">Simple page — we'll be back soon</div>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:border-gray-300 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                    <input type="radio" name="mpage" value="countdown" class="mt-0.5 accent-brand-600" id="countdown-radio">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">Countdown</div>
                        <div class="text-xs text-gray-500 mb-2">Show a live countdown to your launch date</div>
                        <input type="datetime-local" id="countdown-date" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 disabled:opacity-40"
                            value="{{ $site->launch_date ? $site->launch_date->format('Y-m-d\TH:i') : '' }}"
                            min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                    </div>
                </label>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" id="maintenance-cancel" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="button" id="maintenance-confirm" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 transition">Enable</button>
            </div>
        </div>
    </div>
</div>

@if($needsLaravelSetup)
<div class="rounded-xl border border-amber-200 bg-amber-50 p-5 mb-6 flex items-start gap-4">
    <svg class="h-5 w-5 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
    <div class="flex-1">
        <p class="text-sm font-semibold text-amber-900 mb-1">Laravel setup required</p>
        <p class="text-sm text-amber-700 mb-3">A database and <code class="bg-amber-100 px-1 rounded text-xs">.env</code> are needed before the first release can be built.</p>
        <a href="{{ route('sites.laravel-setup', $site) }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
            Set up database &amp; build first release
        </a>
    </div>
</div>
@endif

@if(!$site->github_repo)
@include('sites._github-panel', ['startOpen' => true])
<div class="flex items-center gap-4 mb-6 px-2">
    <div class="flex-1 border-t border-gray-200"></div>
    <span class="text-xs font-medium text-gray-400 uppercase tracking-widest">or</span>
    <div class="flex-1 border-t border-gray-200"></div>
</div>
@include('sites._zip-panel', ['startOpen' => false])
@endif

{{-- Releases + Preview --}}
@if($site->releases->isNotEmpty())
@php
    $sortedReleases = $site->releases->sortByDesc('version');
    $firstRelease = $sortedReleases->first();
    $hasDomain = $site->domain && $site->domain_status === 'active';
    $fmtBytes = fn(int $b): string => match(true) {
        $b >= 1073741824 => round($b / 1073741824, 1) . ' GB',
        $b >= 1048576    => round($b / 1048576, 1) . ' MB',
        $b >= 1024       => round($b / 1024, 1) . ' KB',
        default          => $b . ' B',
    };
    $totalDisk = array_sum($diskUsage);
    // Default preview: staging URL always mirrors the live symlink exactly (coming soon, live release, or maintenance)
    $defaultPreviewSrc = $site->stagingUrl();
    $liveUrl = $hasDomain ? 'https://' . $site->domain : $defaultPreviewSrc;
    $defaultLabel = $site->live_release ? 'Live site' : 'Coming soon';
    if ($site->maintenance_mode) {
        $defaultBadgeClass = 'bg-amber-100 text-amber-700';
        $defaultDotClass   = 'bg-amber-500';
        $defaultBadgeText  = 'maintenance';
        $defaultHeaderBg   = 'rgba(254,243,199,0.35)';
    } elseif ($site->live_release) {
        $defaultBadgeClass = 'bg-emerald-100 text-emerald-700';
        $defaultDotClass   = 'bg-emerald-500';
        $defaultBadgeText  = 'live';
        $defaultHeaderBg   = 'rgba(209,250,229,0.35)';
    } else {
        $defaultBadgeClass = 'bg-gray-100 text-gray-500';
        $defaultDotClass   = 'bg-gray-400';
        $defaultBadgeText  = 'coming soon';
        $defaultHeaderBg   = 'rgba(243,244,246,0.6)';
    }
@endphp
<div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6 overflow-hidden">
    {{-- Section header --}}
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h2 class="text-base font-semibold text-gray-900">Releases</h2>
            @if($totalDisk > 0)
                <span class="text-xs text-gray-400">{{ $fmtBytes($totalDisk) }} total</span>
            @endif
        </div>
        @if($sortedReleases->where('version', '!=', $site->live_release)->isNotEmpty())
            <button type="button" onclick="toggleManageReleases()"
                id="manage-releases-btn"
                class="text-xs text-gray-400 hover:text-gray-600 transition">Manage space</button>
        @endif
    </div>

    {{-- Preview panel --}}
    <div>
        <div id="preview-header" class="px-4 py-2 border-b border-gray-100 transition-colors"
             style="background-color: {{ $defaultHeaderBg }}">
            {{-- Row 1: preview state + open link --}}
            <div class="flex items-center justify-between gap-2 min-w-0">
                <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                    <span class="text-xs font-medium text-gray-400 hidden sm:inline">Previewing</span>
                    <span id="preview-label" class="text-xs font-semibold text-gray-900 truncate hidden sm:inline">{{ $defaultLabel }}</span>
                    <span id="preview-state-badge" class="{{ $defaultBadgeClass }} inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold flex-shrink-0">
                        <span class="h-1.5 w-1.5 rounded-full {{ $defaultDotClass }}"></span>
                        {{ $defaultBadgeText }}
                    </span>
                    <span id="preview-maintenance-warning" class="hidden inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 flex-shrink-0">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>maintenance
                    </span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button id="view-live-btn" onclick="resetPreview()" class="text-xs font-semibold text-gray-400 hover:text-gray-700 transition" title="Show live site in preview">Live</button>
                    {{-- Desktop / Mobile toggle --}}
                    <div class="flex items-center gap-0.5 rounded-lg bg-gray-200 p-0.5">
                        <button id="view-desktop-btn" onclick="setView('desktop')" class="rounded-md px-2 py-1 text-xs font-medium bg-white text-gray-900 shadow-sm transition">Desktop</button>
                        <button id="view-mobile-btn" onclick="setView('mobile')" class="rounded-md px-2 py-1 text-xs font-medium text-gray-500 hover:text-gray-700 transition">Mobile</button>
                    </div>
                    <button onclick="toggleExpand()" id="expand-btn" class="text-xs text-gray-400 hover:text-gray-600 transition">⤢ Expand</button>
                    <a id="preview-open-link" href="{{ $liveUrl }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:underline flex-shrink-0">↗</a>
                </div>
            </div>
        </div>
        <div id="preview-container" class="relative w-full overflow-hidden transition-all duration-300" style="height: 360px; background: #e5e7eb;">
            {{-- Mobile / iPhone 17 view (hidden by default) --}}
            <div id="phone-view" class="absolute inset-0 hidden overflow-hidden" style="background:#e5e7eb;">
                <div id="phone-frame" style="position:absolute;top:16px;left:50%;margin-left:-207px;width:414px;transform:scale(0.38);transform-origin:top center;">
                    <div style="background:#1c1c1e;border-radius:50px;padding:10px 12px 10px;box-shadow:0 0 0 1px #3a3a3c,inset 0 0 0 1px #444,0 30px 70px rgba(0,0,0,0.6);position:relative;">
                        <div style="position:absolute;left:-3px;top:110px;width:3px;height:36px;background:#3a3a3c;border-radius:2px 0 0 2px;"></div>
                        <div style="position:absolute;left:-3px;top:160px;width:3px;height:32px;background:#3a3a3c;border-radius:2px 0 0 2px;"></div>
                        <div style="position:absolute;left:-3px;top:210px;width:3px;height:64px;background:#3a3a3c;border-radius:2px 0 0 2px;"></div>
                        <div style="position:absolute;right:-3px;top:200px;width:3px;height:80px;background:#3a3a3c;border-radius:0 2px 2px 0;"></div>
                        <div style="width:390px;height:844px;background:#000;border-radius:44px;overflow:hidden;position:relative;">
                            <div style="position:absolute;top:12px;left:50%;transform:translateX(-50%);width:120px;height:34px;background:#000;border-radius:20px;z-index:10;box-shadow:0 0 0 3px #111;pointer-events:none;"></div>
                            <iframe id="preview-mobile-iframe" src="{{ $defaultPreviewSrc }}" style="width:390px;height:844px;border:none;display:block;" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Desktop view (shown by default) --}}
            <iframe id="preview-iframe" src="{{ $defaultPreviewSrc }}" class="absolute border-0"
                style="width:1280px;height:800px;transform:scale(0.45);transform-origin:top left;left:calc(50% - 288px);top:0;"
                loading="lazy"></iframe>
        </div>
    </div>

    {{-- Release rows --}}
    <div class="divide-y divide-gray-100 border-t border-gray-100">
        @if($site->maintenance_mode)
            <div id="maintenance-row"
                class="release-row flex items-center justify-between px-6 py-3 cursor-pointer bg-amber-50/60 transition-colors"
                data-preview-url="{{ $site->stagingUrl() }}"
                data-version="Maintenance"
                data-is-live="false"
                data-is-maintenance="true"
                data-promote-url=""
                onclick="selectRelease(this)">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-amber-700">Maintenance</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>currently serving
                    </span>
                </div>
                <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                    <span class="text-xs text-gray-400 hidden sm:inline">Coming soon page</span>
                    @if($hasDomain)
                        <a href="{{ $liveUrl }}" target="_blank"
                           class="rounded-lg border border-amber-200 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50 transition">Visit ↗</a>
                    @endif
                </div>
            </div>
        @endif
        @foreach($sortedReleases as $release)
            @php
                $isLive = $release->version === $site->live_release;
                $isFailed = $release->failed();
                $releaseBytes = $diskUsage[$release->version] ?? 0;
            @endphp
            <div class="release-row {{ $isFailed ? '' : 'cursor-pointer' }} transition-colors
                    flex flex-col px-6 py-3 {{ $isLive ? 'bg-emerald-50/50' : ($isFailed ? 'bg-red-50/60' : 'hover:bg-gray-50') }}"
                data-preview-url="{{ $isLive ? $liveUrl : 'https://' . $site->slug . '-v' . $release->version . '.' . config('services.cpanel.preview_domain') }}"
                @if($release->preview_shared) data-shared-url="{{ 'https://' . $site->slug . '-v' . $release->version . '.' . config('services.cpanel.preview_domain') . '?token=' . $release->preview_token }}" @endif
                data-version="v{{ $release->version }}"
                data-is-live="{{ $isLive ? 'true' : 'false' }}"
                data-promote-url="{{ route('sites.releases.promote', [$site, $release->version]) }}"
                onclick="selectRelease(this)">
                <div class="flex flex-col gap-1" data-badges>
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-bold text-gray-900">v{{ $release->version }}</span>
                        @if($isFailed)
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>build failed
                            </span>
                        @elseif($isLive)
                            <span class="live-badge inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>live
                            </span>
                        @endif
                        @if($release->notes)
                            @php
                                preg_match('/^([0-9a-f]{7})(: .+)$/', $release->notes, $shaMatch);
                            @endphp
                            <span class="text-sm text-gray-500 hidden sm:inline truncate max-w-xs" title="{{ $release->notes }}">
                                @if($shaMatch && $site->github_repo)
                                    <a href="https://github.com/{{ $site->github_repo }}/commit/{{ $shaMatch[1] }}" target="_blank" onclick="event.stopPropagation()" class="font-mono hover:underline">{{ $shaMatch[1] }}</a>{{ $shaMatch[2] }}
                                @else
                                    {{ $release->notes }}
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2" data-actions onclick="event.stopPropagation()">
                    @if(!$isLive)
                        <input type="checkbox" value="{{ $release->version }}"
                            class="release-checkbox hidden h-4 w-4 rounded border-gray-300 text-red-600 cursor-pointer flex-shrink-0"
                            onclick="updateDeleteSelection()">
                    @endif
                    <span class="text-xs text-gray-400 hidden sm:inline">{{ $release->created_at->diffForHumans() }}</span>
                    @if($releaseBytes > 0)
                        <span class="text-xs text-gray-400 hidden sm:inline">· {{ $fmtBytes($releaseBytes) }}</span>
                    @endif
                    @if(!$isFailed)
                        @php $versionedUrl = 'https://' . $site->slug . '-v' . $release->version . '.' . config('services.cpanel.preview_domain') . '?token=' . $release->preview_token; @endphp
                        @if($release->preview_shared)
                            <div class="hidden sm:inline-flex items-center rounded-lg border border-violet-200 bg-violet-50 overflow-hidden">
                                <button onclick="navigator.clipboard.writeText('{{ $versionedUrl }}').then(() => { this.textContent='✓'; setTimeout(() => this.textContent='⧉ v{{ $release->version }}', 1000) })" class="px-2.5 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-100 transition">⧉ v{{ $release->version }}</button>
                                <button onclick="copyAndUpdate(this, '{{ route('sites.releases.version-preview.regenerate', [$site, $release->version]) }}', 'data-confirm', 'Regenerate link? The old URL will stop working immediately.')"
                                    class="border-l border-violet-200 px-2 py-1 text-xs text-violet-500 hover:bg-violet-100 transition" title="Regenerate link">↻</button>
                                <form method="POST" action="{{ route('sites.releases.version-preview.revoke', [$site, $release->version]) }}" data-confirm="Revoke link? Anyone with the current URL will lose access.">
                                    @csrf
                                    <button type="submit" class="border-l border-violet-200 px-2 py-1 text-xs text-violet-400 hover:bg-violet-100 transition" title="Revoke — rotates token, hides URL">✕</button>
                                </form>
                            </div>
                        @else
                            <button onclick="shareVersionPreview(this, '{{ route('sites.releases.version-preview.share', [$site, $release->version]) }}')"
                                class="hidden sm:inline-flex rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-50 transition">Share v{{ $release->version }}</button>
                        @endif
                        @if($isLive)
                            @if($hasDomain)
                                <a href="{{ $liveUrl }}" target="_blank"
                                   class="visit-live-link rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">Visit live ↗</a>
                            @endif
                            @if($sortedReleases->count() === 1)
                                <form method="POST" action="{{ route('sites.revert-to-coming-soon', $site) }}" data-confirm="Revert to coming soon page? The live site will show the placeholder.">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-500 hover:bg-gray-50 transition">↩ Revert to coming soon</button>
                                </form>
                            @endif
                        @else
                            @php $isRollback = $site->live_release && $release->version < $site->live_release; @endphp
                            <button type="button"
                                class="go-live-btn group rounded-lg px-3 py-1.5 text-xs font-semibold transition
                                    {{ $isRollback
                                        ? 'border border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                        : 'bg-emerald-600 text-white hover:bg-emerald-700' }}"
                                data-url="{{ route('sites.releases.promote', [$site, $release->version]) }}"
                                data-version="{{ $release->version }}"
                                data-go-live-confirm="{{ $isRollback ? 'Roll back to v' . $release->version . '? Visitors will see this older version.' : '' }}"
                                onclick="event.stopPropagation(); goLive(this)">{{ $isRollback ? '⏪' : 'Make Current' }}@if($isRollback)<span class="hidden group-hover:inline ml-1">Rollback</span>@endif</button>
                        @endif
                        <a href="{{ route('sites.download.release', [$site, $release]) }}" title="Download v{{ $release->version }}" onclick="event.stopPropagation()"
                           class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-1.5 text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        </a>
                    @endif
                </div>
            </div>
            @if($isFailed)
                <div class="flex items-start gap-2 px-6 pb-3 -mt-1">
                    <svg class="h-4 w-4 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    <pre class="text-xs text-red-700 whitespace-pre-wrap font-mono leading-relaxed">{{ $release->build_error }}</pre>
                </div>
            @endif
        @endforeach
        <form id="delete-releases-form" method="POST" action="{{ route('sites.releases.delete', $site) }}" class="hidden" data-confirm="Delete selected releases? This cannot be undone.">
            @csrf
            @method('DELETE')
            <div id="delete-releases-checkboxes"></div>
            <div class="px-6 py-3 bg-red-50 border-t border-red-100 flex items-center justify-between gap-4">
                <span class="text-sm text-red-700"><span id="delete-count">0</span> release<span id="delete-plural">s</span> selected</span>
                <button type="submit" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition">Delete selected</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleManageReleases() {
    var managing = document.querySelectorAll('.release-checkbox')[0]?.classList.contains('hidden') === false
        ? true : document.querySelectorAll('.release-checkbox.hidden').length === 0;
    var checkboxes = document.querySelectorAll('.release-checkbox');
    var btn = document.getElementById('manage-releases-btn');
    var entering = checkboxes[0]?.classList.contains('hidden');
    checkboxes.forEach(function(cb) { cb.classList.toggle('hidden', !entering); if (!entering) cb.checked = false; });
    if (btn) btn.textContent = entering ? 'Cancel' : 'Manage space';
    updateDeleteSelection();
}
function updateDeleteSelection() {
    var checked = Array.from(document.querySelectorAll('.release-checkbox:checked'));
    var form = document.getElementById('delete-releases-form');
    var box  = document.getElementById('delete-releases-checkboxes');
    var countEl = document.getElementById('delete-count');
    var pluralEl = document.getElementById('delete-plural');
    box.innerHTML = checked.map(function(cb) { return '<input type="hidden" name="versions[]" value="' + cb.value + '">'; }).join('');
    countEl.textContent = checked.length;
    pluralEl.textContent = checked.length === 1 ? '' : 's';
    form.classList.toggle('hidden', checked.length === 0);
}
function showMaintenanceOptions(onConfirm) {
    var modal    = document.getElementById('maintenance-modal');
    var backdrop = document.getElementById('maintenance-backdrop');
    var cancelBtn  = document.getElementById('maintenance-cancel');
    var confirmBtn = document.getElementById('maintenance-confirm');
    var countdownRadio = document.getElementById('countdown-radio');
    var countdownDate  = document.getElementById('countdown-date');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    function close() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    confirmBtn.onclick = function() {
        var page = document.querySelector('input[name="mpage"]:checked').value;
        if (page === 'countdown' && !countdownDate.value) {
            countdownDate.focus();
            return;
        }
        close();
        onConfirm(page, page === 'countdown' ? countdownDate.value : null);
    };

    cancelBtn.onclick = backdrop.onclick = close;
}

var siteMetaLiveUrl = "{{ addslashes($liveUrl) }}";
var siteMetaDefaultSrc = "{{ addslashes($defaultPreviewSrc) }}";
var siteMetaDefaultLabel = "{{ addslashes($defaultLabel) }}";
var siteMetaLiveVersion = {{ $site->live_release ?? 'null' }};
var siteMaintenanceActive = {{ $site->maintenance_mode ? 'true' : 'false' }};
var siteMetaHasDomain = {{ $hasDomain ? 'true' : 'false' }};

function updatePreviewIndicator(isLive, isMaintenance) {
    var header = document.getElementById('preview-header');
    var badge = document.getElementById('preview-state-badge');
    var warning = document.getElementById('preview-maintenance-warning');
    if (isMaintenance) {
        header.style.backgroundColor = 'rgba(254,243,199,0.35)';
        badge.className = 'bg-amber-100 text-amber-700 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold';
        badge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>maintenance';
        if (warning) warning.classList.add('hidden');
    } else if (isLive) {
        header.style.backgroundColor = 'rgba(209,250,229,0.35)';
        badge.className = 'bg-emerald-100 text-emerald-700 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold';
        badge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>live';
        if (warning) warning.classList.toggle('hidden', !siteMaintenanceActive);
    } else {
        header.style.backgroundColor = 'rgba(243,244,246,0.6)';
        badge.className = 'bg-gray-100 text-gray-500 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold';
        badge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>not live';
        if (warning) warning.classList.add('hidden');
    }
}
function selectRelease(row) {
    var isMaintenance = row.dataset.isMaintenance === 'true';
    var isLive = row.dataset.isLive === 'true';
    document.querySelectorAll('.release-row').forEach(function(r) {
        if (r.dataset.isLive !== 'true' && r.dataset.isMaintenance !== 'true') r.classList.remove('bg-brand-50');
    });
    if (!isLive && !isMaintenance) row.classList.add('bg-brand-50');
    var lBtn = document.getElementById('view-live-btn');
    if (lBtn) lBtn.className = 'rounded-md px-2 py-1 text-xs font-medium transition text-gray-500 hover:text-gray-700';
    var previewSrc = row.dataset.sharedUrl || row.dataset.previewUrl;
    document.getElementById('preview-iframe').src = previewSrc;
    document.getElementById('preview-mobile-iframe').src = previewSrc;
    document.getElementById('preview-open-link').href = isMaintenance ? siteMetaLiveUrl : previewSrc;
    document.getElementById('preview-label').textContent = row.dataset.version;
    updatePreviewIndicator(isLive, isMaintenance);
}
function shareVersionPreview(btn, url) {
    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            navigator.clipboard.writeText(data.url);
            btn.textContent = '✓ Copied!';
            // Reload so shared state is reflected in UI
            setTimeout(() => location.reload(), 800);
        });
}
function copyAndUpdate(btn, url, confirmAttr, confirmMsg) {
    var proceed = function() {
        fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                navigator.clipboard.writeText(data.url);
                btn.textContent = '✓';
                setTimeout(() => { btn.textContent = '↻'; }, 1000);
                // Update the copy button in the same pill with the new URL
                var pill = btn.closest('div');
                if (pill) {
                    var copyBtn = pill.querySelector('button:first-child');
                    if (copyBtn) {
                        var v = copyBtn.textContent.trim().replace('⧉ ', '');
                        copyBtn.setAttribute('onclick', copyBtn.getAttribute('onclick').replace(/writeText\('[^']+'\)/, "writeText('" + data.url + "')"));
                    }
                }
                // Also update the row's data-shared-url
                var row = btn.closest('.release-row');
                if (row) row.dataset.sharedUrl = data.url;
            });
    };
    if (confirmMsg) { window.showConfirm(confirmMsg, proceed); } else { proceed(); }
}
function resetPreview() {
    document.querySelectorAll('.release-row').forEach(function(r) {
        if (r.dataset.isLive !== 'true' && r.dataset.isMaintenance !== 'true') r.classList.remove('bg-brand-50');
    });
    document.getElementById('preview-iframe').src = siteMetaDefaultSrc;
    document.getElementById('preview-mobile-iframe').src = siteMetaDefaultSrc;
    document.getElementById('preview-open-link').href = siteMetaDefaultSrc;
    document.getElementById('preview-label').textContent = siteMetaDefaultLabel;
    updatePreviewIndicator(!!siteMetaLiveVersion, siteMaintenanceActive);
    var lBtn = document.getElementById('view-live-btn');
    if (lBtn) lBtn.className = 'rounded-md px-2 py-1 text-xs font-medium transition bg-white text-gray-900 shadow-sm';
}
function goLive(btn) {
    var msg = btn.dataset.goLiveConfirm;
    if (msg) {
        showConfirm(msg, function () { _doGoLive(btn); });
        return;
    }
    _doGoLive(btn);
}
function _doGoLive(btn) {
    btn.disabled = true;
    btn.textContent = 'Making current…';
    fetch(btn.dataset.url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(function(r) { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(function(data) {
        var newVersion = data.version;
        siteMetaLiveVersion = newVersion;
        document.querySelectorAll('.release-row').forEach(function(row) {
            var v = parseInt(row.dataset.version.replace('v', ''));
            var badge = row.querySelector('.live-badge');
            var goLiveBtn = row.querySelector('.go-live-btn');
            var visitLink = row.querySelector('.visit-live-link');
            var actionsDiv = row.querySelector('[data-actions]');
            if (v === newVersion) {
                row.classList.add('bg-emerald-50/50');
                row.classList.remove('bg-brand-50', 'hover:bg-gray-50');
                row.dataset.isLive = 'true';
                if (!badge) {
                    row.querySelector('[data-badges]').insertAdjacentHTML('beforeend',
                        '<span class="live-badge inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>live</span>');
                }
                if (goLiveBtn) goLiveBtn.remove();
                if (!visitLink && actionsDiv && siteMetaHasDomain) {
                    actionsDiv.insertAdjacentHTML('beforeend',
                        '<a href="' + siteMetaLiveUrl + '" target="_blank" class="visit-live-link rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">Visit live \u2197</a>');
                }
            } else if (badge) {
                badge.remove();
                row.classList.remove('bg-emerald-50/50');
                row.classList.add('hover:bg-gray-50');
                row.dataset.isLive = 'false';
                if (visitLink) visitLink.remove();
                if (actionsDiv && !row.querySelector('.go-live-btn')) {
                    actionsDiv.insertAdjacentHTML('beforeend',
                        '<button type="button" class="go-live-btn rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition" data-url="' + row.dataset.promoteUrl + '" data-version="' + v + '" onclick="event.stopPropagation(); goLive(this)">Make Current</button>');
                }
            }
        });
        // Remove maintenance row and turn off maintenance flag
        var maintenanceRow = document.getElementById('maintenance-row');
        if (maintenanceRow) maintenanceRow.remove();
        siteMaintenanceActive = false;
        // Update page header badge
        var siteBadge = document.getElementById('site-header-badge');
        if (siteBadge) {
            siteBadge.className = 'inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700';
            siteBadge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span><span id="live-status-badge">Release ' + newVersion + ' live</span>';
        }
        var serving = document.getElementById('currently-serving');
        if (serving) serving.textContent = 'Release ' + newVersion;
        // Select the newly promoted row so the preview updates to it
        var promotedRow = null;
        document.querySelectorAll('.release-row').forEach(function(row) {
            if (parseInt(row.dataset.version.replace('v', '')) === newVersion) promotedRow = row;
        });
        if (promotedRow) selectRelease(promotedRow);
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = 'Make Current';
    });
}
var currentView = 'desktop';
function setView(view) {
    currentView = view;
    var phoneView = document.getElementById('phone-view');
    var desktopIframe = document.getElementById('preview-iframe');
    var dBtn = document.getElementById('view-desktop-btn');
    var mBtn = document.getElementById('view-mobile-btn');
    var lBtn = document.getElementById('view-live-btn');
    var active = 'bg-white text-gray-900 shadow-sm';
    var inactive = 'text-gray-500 hover:text-gray-700';
    dBtn.className = 'rounded-md px-2 py-1 text-xs font-medium transition ' + inactive;
    mBtn.className = 'rounded-md px-2 py-1 text-xs font-medium transition ' + inactive;
    lBtn.className = 'rounded-md px-2 py-1 text-xs font-medium transition ' + inactive;
    if (view === 'mobile') {
        phoneView.classList.remove('hidden');
        desktopIframe.style.visibility = 'hidden';
        mBtn.className = 'rounded-md px-2 py-1 text-xs font-medium transition ' + active;
    } else {
        phoneView.classList.add('hidden');
        desktopIframe.style.visibility = '';
        dBtn.className = 'rounded-md px-2 py-1 text-xs font-medium transition ' + active;
    }
}
function toggleExpand() {
    // On mobile: open fullscreen overlay instead of resizing the container
    if (window.innerWidth < 768) {
        var src = currentView === 'mobile'
            ? document.getElementById('preview-mobile-iframe').src
            : document.getElementById('preview-iframe').src;
        openFullscreenPreview(src);
        return;
    }
    var c = document.getElementById('preview-container');
    var btn = document.getElementById('expand-btn');
    var expanded = c.style.height === '700px';
    if (currentView === 'desktop') {
        var f = document.getElementById('preview-iframe');
        if (!expanded) {
            c.style.height = '700px';
            f.style.transform = 'scale(0.875)';
            f.style.left = 'calc(50% - 560px)';
        } else {
            c.style.height = '360px';
            f.style.transform = 'scale(0.45)';
            f.style.left = 'calc(50% - 288px)';
        }
    } else {
        var pf = document.getElementById('phone-frame');
        if (!expanded) {
            c.style.height = '700px';
            pf.style.transform = 'scale(0.72)';
        } else {
            c.style.height = '360px';
            pf.style.transform = 'scale(0.38)';
        }
    }
    btn.textContent = expanded ? '⤢ Expand' : '⤡ Collapse';
}
function openFullscreenPreview(src) {
    var overlay = document.getElementById('fs-overlay');
    var iframe  = document.getElementById('fs-iframe');
    var urlBar  = document.getElementById('fs-url-bar');
    iframe.src = src;
    urlBar.textContent = src.replace(/^https?:\/\//, '');
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeFullscreenPreview() {
    var overlay = document.getElementById('fs-overlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
    document.getElementById('fs-iframe').src = '';
    document.body.style.overflow = '';
}
// Auto-switch to mobile view on small screens
if (window.innerWidth < 768) { setView('mobile'); }
</script>
@else
<div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Releases</h2>
    </div>
    <div class="p-8 text-center">
        @if($needsLaravelSetup)
            <p class="text-sm text-gray-500">No releases yet. Complete the Laravel setup above to build the first one.</p>
        @elseif($site->github_repo)
            <p class="text-sm text-gray-500 mb-4">No releases yet. Push to <span class="font-medium">{{ $site->github_branch ?? 'the default branch' }}</span> to create one automatically, or import the latest now.</p>
            <form method="POST" action="{{ route('github.create-first-release', $site) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.298 24 12c0-6.627-5.373-12-12-12"/></svg>
                    Import latest from {{ $site->github_branch ?? 'default branch' }}
                </button>
            </form>
        @else
            <p class="text-sm text-gray-500">No releases yet. Upload a .zip or connect a GitHub repository above.</p>
        @endif
    </div>
</div>
@endif

@if($site->github_repo)
@include('sites._github-panel', ['startOpen' => false])
<div class="flex items-center gap-4 mb-6 px-2">
    <div class="flex-1 border-t border-gray-200"></div>
    <span class="text-xs font-medium text-gray-400 uppercase tracking-widest">or</span>
    <div class="flex-1 border-t border-gray-200"></div>
</div>
@include('sites._zip-panel', ['startOpen' => false])
@endif

{{-- Admin Access --}}
@include('sites._admin-access-panel')

{{-- Domain --}}
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm mb-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-50">
            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
        </div>
        <h2 class="text-base font-semibold text-gray-900">Custom Domain</h2>
    </div>

    @if(!$site->domain)
        {{-- No domain: attach form --}}
        <form method="POST" action="{{ route('sites.domain.attach', $site) }}" class="flex flex-col sm:flex-row gap-3" id="attach-domain-form" onsubmit="startAttaching(this)">
            @csrf
            <input type="text" name="domain" id="domain-input" placeholder="yoursite.com"
                value="{{ old('domain') }}"
                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 {{ $errors->has('domain') ? 'border-red-400' : '' }}">
            <button type="submit" id="attach-btn" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition whitespace-nowrap">
                Attach domain
            </button>
        </form>
        <p id="attach-status" class="mt-2 text-xs text-gray-500 hidden"></p>
        @error('domain')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @if(auth()->user()->is_admin)
        <details class="mt-4">
            <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600 select-none">Admin: manually connect domain (no cPanel)</summary>
            <form method="POST" action="{{ route('sites.domain.force-active', $site) }}" class="flex gap-2 mt-2">
                @csrf
                <input type="text" name="domain" placeholder="traitor.dev"
                    value="{{ old('force_domain') }}"
                    class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 {{ $errors->has('force_domain') ? 'border-red-400' : '' }}">
                <button type="submit" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition whitespace-nowrap">Mark active</button>
            </form>
            @error('force_domain')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </details>
        @endif
        <script>
        function startAttaching(form) {
            var input = document.getElementById('domain-input');
            var btn   = document.getElementById('attach-btn');
            var status = document.getElementById('attach-status');
            input.readOnly = true;
            input.classList.add('bg-gray-50', 'text-gray-400');
            btn.disabled = true;
            btn.textContent = 'Attaching…';
            btn.classList.remove('hover:bg-brand-700');
            btn.classList.add('opacity-60', 'cursor-not-allowed');
            status.textContent = 'Registering domain with the web server — this takes a few seconds…';
            status.classList.remove('hidden');
        }
        </script>

    @elseif($site->domain_status === 'pending_dns')
        {{-- Waiting for DNS --}}
        <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>Waiting for DNS
            </span>
            <span class="text-sm font-medium text-gray-900">{{ $site->domain }}</span>
        </div>
        <p class="text-sm text-gray-500 mb-3">Point your domain's A record to <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-mono">{{ config('app.server_ip') }}</code> then click Check DNS.</p>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-mono text-gray-700 space-y-1 mb-4">
            <div><span class="text-gray-400 mr-4">@</span>A<span class="float-right">{{ config('app.server_ip') }}</span></div>
            <div><span class="text-gray-400 mr-2">www</span>A<span class="float-right">{{ config('app.server_ip') }}</span></div>
        </div>
        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('sites.domain.check-dns', $site) }}">
                @csrf
                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">Check DNS</button>
            </form>
            <form method="POST" action="{{ route('sites.domain.detach', $site) }}" data-confirm="Remove {{ $site->domain }}? The domain will be detached from cPanel.">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition">Remove domain</button>
            </form>
        </div>

    @elseif($site->domain_status === 'active')
        {{-- Domain active --}}
        <div class="flex items-center gap-3 mb-2">
            @if($site->maintenance_mode)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Maintenance
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active
                </span>
            @endif
            <a href="https://{{ $site->domain }}" target="_blank" class="text-sm font-medium text-brand-600 hover:underline">{{ $site->domain }}</a>
        </div>
        <p class="text-sm text-gray-500 mb-4">
            Currently serving:
            @if($site->maintenance_mode)
                <span class="text-amber-600 font-medium">Coming Soon page (maintenance mode)</span>
            @elseif($site->live_release)
                <span id="currently-serving" class="text-emerald-700 font-medium">Release {{ $site->live_release }}</span>
            @else
                <span class="text-gray-500 font-medium">Coming Soon page — press Make Current on a release to publish</span>
            @endif
        </p>
        <div class="flex justify-end">
            <form method="POST" action="{{ route('sites.domain.detach', $site) }}" data-confirm="Remove {{ $site->domain }}? The domain will be detached from cPanel.">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition">Remove domain</button>
            </form>
        </div>
    @endif
</div>

<script>
function startRename() {
    document.getElementById('site-name-display').classList.add('hidden');
    var form = document.getElementById('rename-form');
    form.classList.remove('hidden');
    form.classList.add('flex');
    var input = document.getElementById('rename-input');
    input.focus();
    input.select();
}
function cancelRename() {
    document.getElementById('rename-form').classList.add('hidden');
    document.getElementById('rename-form').classList.remove('flex');
    document.getElementById('site-name-display').classList.remove('hidden');
}
function restartApp() {
    var btn = document.getElementById('restart-btn');
    var icon = document.getElementById('restart-icon');
    btn.disabled = true;
    icon.classList.add('animate-spin');

    fetch('{{ route('sites.restart', $site) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var output = document.getElementById('artisan-output');
        output.textContent = '$ php artisan optimize:clear\n' + (data.output || '(no output)') + '\n[exit ' + data.exitCode + ']';
        output.classList.remove('hidden');
    })
    .catch(function() { alert('Restart failed.'); })
    .finally(function() {
        btn.disabled = false;
        icon.classList.remove('animate-spin');
    });
}
var artisanLoaded = false;
var artisanRunning = false;
var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';

var DESTRUCTIVE = ['migrate:fresh','migrate:reset','migrate:rollback','db:wipe','queue:flush','cache:clear','config:clear','route:clear','view:clear','optimize:clear'];

function loadArtisan(btn) {
    if (artisanLoaded) {
        document.getElementById('artisan-groups').classList.toggle('hidden');
        return;
    }
    btn.textContent = 'Loading…';
    btn.disabled = true;
    document.getElementById('artisan-loading').classList.remove('hidden');

    fetch('{{ route('sites.artisan.commands', $site) }}')
        .then(function(r) { return r.json(); })
        .then(function(groups) {
            artisanLoaded = true;
            document.getElementById('artisan-loading').classList.add('hidden');
            btn.textContent = 'Hide';
            btn.disabled = false;
            btn.onclick = function() {
                document.getElementById('artisan-groups').classList.toggle('hidden');
                btn.textContent = document.getElementById('artisan-groups').classList.contains('hidden') ? 'Show' : 'Hide';
            };

            var container = document.getElementById('artisan-groups');
            container.innerHTML = '';

            Object.keys(groups).forEach(function(ns) {
                var label = ns === '' ? 'General' : ns;
                var section = document.createElement('div');
                section.innerHTML = '<p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">' + label + '</p>';
                var wrap = document.createElement('div');
                wrap.className = 'flex flex-wrap gap-2';
                groups[ns].forEach(function(cmd) {
                    var btn = document.createElement('button');
                    var isDestructive = DESTRUCTIVE.indexOf(cmd.name) !== -1;
                    btn.type = 'button';
                    btn.title = cmd.description;
                    btn.textContent = cmd.name;
                    btn.className = 'rounded px-2.5 py-1 text-xs font-mono font-medium transition ' +
                        (isDestructive
                            ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200');
                    btn.onclick = function() { runArtisan(cmd.name, isDestructive, btn); };
                    wrap.appendChild(btn);
                });
                section.appendChild(wrap);
                container.appendChild(section);
            });

            container.classList.remove('hidden');
        })
        .catch(function(e) {
            document.getElementById('artisan-loading').classList.add('hidden');
            document.getElementById('artisan-error').textContent = 'Failed to load commands.';
            document.getElementById('artisan-error').classList.remove('hidden');
            btn.textContent = 'Retry';
            btn.disabled = false;
        });
}

function runArtisan(command, isDestructive, btn) {
    if (artisanRunning) return;
    if (isDestructive && !confirm('Run "php artisan ' + command + '"? This is a destructive command.')) return;

    artisanRunning = true;
    var orig = btn.textContent;
    btn.textContent = '…';
    btn.disabled = true;

    var output = document.getElementById('artisan-output');
    output.textContent = '$ php artisan ' + command + '\n';
    output.classList.remove('hidden');

    fetch('{{ route('sites.artisan.run', $site) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ command: command }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        output.textContent += data.output || '(no output)';
        output.textContent += '\n[exit ' + data.exitCode + ']';
        output.scrollTop = output.scrollHeight;
    })
    .catch(function() { output.textContent += '\nRequest failed.'; })
    .finally(function() {
        artisanRunning = false;
        btn.textContent = orig;
        btn.disabled = false;
    });
}
function toggleEnvEditor() {
    var el = document.getElementById('env-editor');
    var btn = document.getElementById('env-toggle');
    el.classList.toggle('hidden');
    btn.textContent = el.classList.contains('hidden') ? 'Show' : 'Hide';
}
function toggleLog(id) {
    var el = document.getElementById(id);
    var chevron = document.getElementById(id + '-chevron');
    el.classList.toggle('hidden');
    chevron.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
}
function copyLog(btn, text) {
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg class="h-3.5 w-3.5 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>';
        setTimeout(function() { btn.innerHTML = orig; }, 1500);
    });
}
</script>

{{-- .env Editor (Laravel only) --}}
@if($site->type === 'laravel' && $envContent !== null)
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-700">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Environment</h2>
                <p class="text-xs text-gray-400">shared/.env — shared across all releases</p>
            </div>
        </div>
        <button type="button" onclick="toggleEnvEditor()" class="text-xs text-gray-400 hover:text-gray-600" id="env-toggle">Show</button>
    </div>

    <div id="env-editor" class="hidden">
        <form method="POST" action="{{ route('sites.env.update', $site) }}">
            @csrf
            @method('PUT')
            <textarea name="env_content" rows="16"
                class="w-full rounded-lg border border-gray-200 bg-gray-950 px-4 py-3 text-xs font-mono text-green-400 focus:outline-none focus:ring-1 focus:ring-brand-500 resize-y">{{ $envContent }}</textarea>
            <div class="flex items-center gap-3 mt-3">
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">Save .env</button>
                <span class="text-xs text-gray-400">Changes take effect immediately — no rebuild needed.</span>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Artisan Commands (Laravel only) --}}
@if($site->type === 'laravel' && $envContent !== null)
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Artisan</h2>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" id="restart-btn" onclick="restartApp()" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                <svg id="restart-icon" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                Restart
            </button>
            <button type="button" onclick="loadArtisan(this)" class="text-xs text-gray-400 hover:text-gray-600">Load commands</button>
        </div>
    </div>

    <div id="artisan-output" class="hidden mb-4 rounded-lg bg-gray-950 px-4 py-3 text-xs font-mono text-green-400 whitespace-pre-wrap break-all max-h-64 overflow-y-auto"></div>

    <div id="artisan-groups" class="hidden space-y-3"></div>
    <p id="artisan-loading" class="hidden text-sm text-gray-400">Loading commands…</p>
    <p id="artisan-error" class="hidden text-sm text-red-500"></p>
</div>
@endif

{{-- Application Logs (Laravel only) --}}
@if($site->type === 'laravel')
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Application Logs</h2>
        </div>
        <a href="{{ route('sites.show', $site) }}#logs" onclick="location.reload()" class="text-xs text-gray-400 hover:text-gray-600">Refresh</a>
    </div>

    @if(empty($logEntries))
    <p class="text-sm text-gray-400 font-mono">No log entries found.</p>
    @else
    <div id="logs" class="space-y-1">
        @foreach($logEntries as $i => $entry)
        @php
            $levelColour = match($entry['level']) {
                'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'text-red-400',
                'WARNING' => 'text-yellow-400',
                'NOTICE', 'INFO' => 'text-blue-400',
                default => 'text-gray-400',
            };
            $hasTrace = !empty($entry['trace']);
            $entryId  = 'log-' . $i;
        @endphp
        <div class="rounded-lg bg-gray-950 text-xs font-mono overflow-hidden">
            <div class="flex items-start gap-2 px-3 py-2 {{ $hasTrace ? 'cursor-pointer select-none' : '' }}"
                 {{ $hasTrace ? "onclick=\"toggleLog('{$entryId}')\"" : '' }}>
                <span class="shrink-0 text-gray-500">{{ $entry['timestamp'] }}</span>
                <span class="shrink-0 font-semibold {{ $levelColour }}">{{ $entry['level'] }}</span>
                <span class="flex-1 text-gray-200 break-all">{{ $entry['message'] }}</span>
                <button type="button"
                    onclick="event.stopPropagation(); copyLog(this, {{ json_encode($entry['timestamp'] . ' ' . $entry['level'] . ': ' . $entry['message']) }})"
                    class="shrink-0 text-gray-600 hover:text-gray-300 transition ml-1" title="Copy">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                </button>
                @if($hasTrace)
                <svg id="{{ $entryId }}-chevron" class="h-3.5 w-3.5 shrink-0 text-gray-600 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                @endif
            </div>
            @if($hasTrace)
            <div id="{{ $entryId }}" class="hidden border-t border-gray-800 px-3 py-2 text-gray-500 whitespace-pre-wrap break-all">{{ implode("\n", $entry['trace']) }}</div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- Danger Zone --}}
<div class="rounded-xl border border-red-200 bg-white p-6 shadow-sm">
    <div class="flex items-center gap-3 mb-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50">
            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
        </div>
        <h2 class="text-base font-semibold text-gray-900">Danger Zone</h2>
    </div>
    <p class="text-sm text-gray-500 mb-4 ml-12">Permanently delete this site and all its releases.</p>
    <form action="{{ route('sites.destroy', $site) }}" method="POST" class="ml-12" data-confirm="Delete {{ $site->name }}? This cannot be undone — all releases will be permanently removed.">
        @csrf
        @method('DELETE')
        @if($site->type === 'laravel')
        <label class="flex items-center gap-2 mb-3 cursor-pointer">
            <input type="checkbox" name="drop_database" value="1" class="rounded border-gray-300 text-red-600">
            <span class="text-sm text-gray-600">Also delete the MySQL database and user</span>
        </label>
        @endif
        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">Delete Site</button>
    </form>
</div>

{{-- Fullscreen preview overlay (mobile expand) --}}
<div id="fs-overlay" class="fixed inset-0 z-50 hidden flex-col bg-white" style="flex-direction:column;">
    <div class="flex items-center gap-2 px-3 py-2 border-b border-gray-200 bg-gray-50 flex-shrink-0" style="min-height:52px;">
        <button onclick="closeFullscreenPreview()" class="flex-shrink-0 rounded-full p-2 text-gray-500 active:bg-gray-100" aria-label="Close">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
        <div id="fs-url-bar" class="flex-1 rounded-full bg-gray-100 px-3 py-1.5 text-sm text-gray-600 truncate"></div>
        @if($site->domain)
        <a id="fs-open-link" href="https://{{ $site->domain }}" target="_blank" class="flex-shrink-0 rounded-full p-2 text-brand-600" aria-label="Open in browser">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
        </a>
        @endif
    </div>
    <iframe id="fs-iframe" src="" class="border-0 w-full" style="display:block;flex:1;min-height:0;"></iframe>
</div>
@endsection
