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
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Not published
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
    @if($site->domain_status === 'active')
    <form method="POST" action="{{ route('sites.maintenance.toggle', $site) }}" class="flex-shrink-0">
        @csrf
        <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-semibold transition
            {{ $site->maintenance_mode
                ? 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                : 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
            {{ $site->maintenance_mode ? 'Bring back online' : 'Enable maintenance mode' }}
        </button>
    </form>
    @endif
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

{{-- Releases + Preview --}}
@if($site->releases->isNotEmpty())
@php
    $sortedReleases = $site->releases->sortByDesc('version');
    $firstRelease = $sortedReleases->first();
    $hasDomain = $site->domain && $site->domain_status === 'active';
    $liveUrl = $hasDomain ? 'https://' . $site->domain : $site->previewUrl();
    // Default preview = portal preview of the live/ symlink (always current, no release-specific)
    $defaultPreviewSrc = $site->previewUrl();
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
<div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6">
    {{-- Section header --}}
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Releases</h2>
    </div>

    {{-- Preview panel --}}
    <div>
        <div id="preview-header" class="px-6 py-3 border-b border-gray-100 flex items-center justify-between transition-colors"
             style="background-color: {{ $defaultHeaderBg }}">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Previewing</span>
                <span id="preview-label" class="text-xs font-semibold text-gray-900">Live site</span>
                <span id="preview-state-badge" class="{{ $defaultBadgeClass }} inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold">
                    <span class="h-1.5 w-1.5 rounded-full {{ $defaultDotClass }}"></span>
                    {{ $defaultBadgeText }}
                </span>
                <span id="preview-maintenance-warning" class="hidden inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>maintenance active
                </span>
                <button onclick="resetPreview()" class="text-xs text-gray-400 hover:text-gray-600 transition ml-1" title="Back to live site">↺</button>
            </div>
            <div class="flex items-center gap-3">
                {{-- Mobile / Desktop toggle --}}
                <div class="flex items-center gap-0.5 rounded-lg bg-gray-200 p-0.5">
                    <button id="view-desktop-btn" onclick="setView('desktop')" class="rounded-md px-2.5 py-1 text-xs font-medium bg-white text-gray-900 shadow-sm transition">Desktop</button>
                    <button id="view-mobile-btn" onclick="setView('mobile')" class="rounded-md px-2.5 py-1 text-xs font-medium text-gray-500 hover:text-gray-700 transition">Mobile</button>
                </div>
                <button onclick="toggleExpand()" id="expand-btn" class="text-xs text-gray-400 hover:text-gray-600 transition">⤢ Expand</button>
                <a id="preview-open-link" href="{{ $liveUrl }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:underline">Open ↗</a>
            </div>
        </div>
        <div id="preview-container" class="relative w-full overflow-hidden transition-all duration-300" style="height: 360px; background: #e5e7eb;">
            {{-- Mobile / iPhone 17 view (hidden by default) --}}
            <div id="phone-view" class="absolute inset-0 hidden" style="background:#e5e7eb;">
                <div id="phone-frame" style="position:absolute;top:16px;left:calc(50% - 207px);width:414px;transform:scale(0.38);transform-origin:top center;">
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
                data-preview-url="{{ $site->previewUrl() }}"
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
                <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                    <span class="text-xs text-gray-400">Coming soon page</span>
                    @if($hasDomain)
                        <a href="{{ $liveUrl }}" target="_blank"
                           class="rounded-lg border border-amber-200 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50 transition">Visit site ↗</a>
                    @endif
                </div>
            </div>
        @endif
        @foreach($sortedReleases as $release)
            @php $isLive = $release->version === $site->live_release; @endphp
            <div class="release-row flex items-center justify-between px-6 py-3 cursor-pointer transition-colors
                    {{ $isLive ? 'bg-emerald-50/50' : 'hover:bg-gray-50' }}"
                data-preview-url="{{ $release->previewUrl() }}"
                data-version="v{{ $release->version }}"
                data-is-live="{{ $isLive ? 'true' : 'false' }}"
                data-promote-url="{{ route('sites.releases.promote', [$site, $release->version]) }}"
                onclick="selectRelease(this)">
                <div class="flex items-center gap-3" data-badges>
                    <span class="font-mono text-sm font-bold text-gray-900">v{{ $release->version }}</span>
                    @if($isLive)
                        <span class="live-badge inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>live
                        </span>
                    @endif
                    @if($release->notes)
                        <span class="text-sm text-gray-500 hidden sm:inline">{{ $release->notes }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-3" data-actions onclick="event.stopPropagation()">
                    <span class="text-xs text-gray-400">{{ $release->created_at->diffForHumans() }}</span>
                    <a href="{{ route('sites.download.release', [$site, $release]) }}" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-50 transition">Download</a>
                    @if($isLive && $hasDomain)
                        <a href="{{ $liveUrl }}" target="_blank"
                           class="visit-live-link rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">Visit live ↗</a>
                    @elseif(!$isLive && $hasDomain)
                        <button type="button"
                            class="go-live-btn rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                            data-url="{{ route('sites.releases.promote', [$site, $release->version]) }}"
                            data-version="{{ $release->version }}"
                            onclick="event.stopPropagation(); goLive(this)">Go Live</button>
                    @elseif(!$hasDomain && $loop->first)
                        <a href="{{ route('sites.show', $site) }}#domain" class="text-xs text-brand-600 hover:underline" onclick="event.stopPropagation()">Add a domain to go live ↓</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
var siteMetaLiveUrl = "{{ addslashes($liveUrl) }}";
var siteMetaDefaultSrc = "{{ addslashes($defaultPreviewSrc) }}";
var siteMetaLiveVersion = {{ $site->live_release ?? 'null' }};
var siteMaintenanceActive = {{ $site->maintenance_mode ? 'true' : 'false' }};

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
        header.style.backgroundColor = 'rgba(254,243,199,0.35)';
        badge.className = 'bg-amber-100 text-amber-700 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold';
        badge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>candidate';
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
    var previewSrc = row.dataset.previewUrl;
    document.getElementById('preview-iframe').src = previewSrc;
    document.getElementById('preview-mobile-iframe').src = previewSrc;
    document.getElementById('preview-open-link').href = isMaintenance ? siteMetaLiveUrl : previewSrc;
    document.getElementById('preview-label').textContent = row.dataset.version;
    updatePreviewIndicator(isLive, isMaintenance);
}
function resetPreview() {
    document.querySelectorAll('.release-row').forEach(function(r) {
        if (r.dataset.isLive !== 'true' && r.dataset.isMaintenance !== 'true') r.classList.remove('bg-brand-50');
    });
    document.getElementById('preview-iframe').src = siteMetaDefaultSrc;
    document.getElementById('preview-mobile-iframe').src = siteMetaDefaultSrc;
    document.getElementById('preview-open-link').href = siteMetaLiveUrl;
    document.getElementById('preview-label').textContent = 'Live site';
    updatePreviewIndicator(false, siteMaintenanceActive);
}
function goLive(btn) {
    btn.disabled = true;
    btn.textContent = 'Going live…';
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
                if (!visitLink && actionsDiv) {
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
                        '<button type="button" class="go-live-btn rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition" data-url="' + row.dataset.promoteUrl + '" data-version="' + v + '" onclick="event.stopPropagation(); goLive(this)">Go Live</button>');
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
        btn.textContent = 'Go Live';
    });
}
var currentView = 'desktop';
function setView(view) {
    currentView = view;
    var phoneView = document.getElementById('phone-view');
    var desktopIframe = document.getElementById('preview-iframe');
    var dBtn = document.getElementById('view-desktop-btn');
    var mBtn = document.getElementById('view-mobile-btn');
    var active = 'bg-white text-gray-900 shadow-sm';
    var inactive = 'text-gray-500 hover:text-gray-700';
    if (view === 'mobile') {
        phoneView.classList.remove('hidden');
        desktopIframe.style.visibility = 'hidden';
        mBtn.className = 'rounded-md px-2.5 py-1 text-xs font-medium transition ' + active;
        dBtn.className = 'rounded-md px-2.5 py-1 text-xs font-medium transition ' + inactive;
    } else {
        phoneView.classList.add('hidden');
        desktopIframe.style.visibility = '';
        dBtn.className = 'rounded-md px-2.5 py-1 text-xs font-medium transition ' + active;
        mBtn.className = 'rounded-md px-2.5 py-1 text-xs font-medium transition ' + inactive;
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
        <p class="text-sm text-gray-500">No releases yet. Upload a .zip above to create the first one.</p>
    </div>
</div>
@endif

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
        <form method="POST" action="{{ route('sites.domain.attach', $site) }}" class="flex gap-3" id="attach-domain-form" onsubmit="startAttaching(this)">
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
        <script>
        function startAttaching(form) {
            var input = document.getElementById('domain-input');
            var btn   = document.getElementById('attach-btn');
            var status = document.getElementById('attach-status');
            input.disabled = true;
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
            <form method="POST" action="{{ route('sites.domain.detach', $site) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition" onclick="return confirm('Remove {{ $site->domain }}?')">Remove domain</button>
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
                <span class="text-gray-500 font-medium">Coming Soon page — press Go Live on a release to publish</span>
            @endif
        </p>
        <div class="flex justify-end">
            <form method="POST" action="{{ route('sites.domain.detach', $site) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition" onclick="return confirm('Remove {{ $site->domain }}?')">Remove domain</button>
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
</script>

{{-- Danger Zone --}}
<div class="rounded-xl border border-red-200 bg-white p-6 shadow-sm">
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
