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
            @if($site->live_release)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Release {{ $site->live_release }} live
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
@php $sortedReleases = $site->releases->sortByDesc('version'); $firstRelease = $sortedReleases->first(); @endphp
<div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6">
    {{-- Section header --}}
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900">Releases</h2>
        @if($site->domain_status === 'active')
            <form method="POST" action="{{ route('sites.maintenance.toggle', $site) }}">
                @csrf
                <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                    {{ $site->maintenance_mode
                        ? 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                        : 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    {{ $site->maintenance_mode ? 'Bring back online' : 'Maintenance mode' }}
                </button>
            </form>
        @endif
    </div>

    {{-- Preview panel --}}
    <div>
        <div class="px-6 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Previewing</span>
                <span id="preview-label" class="text-xs font-semibold text-gray-900">v{{ $firstRelease->version }}</span>
                <button onclick="resetPreview()" class="text-xs text-gray-400 hover:text-gray-600 transition ml-1">↺</button>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="toggleExpand()" id="expand-btn" class="text-xs text-gray-400 hover:text-gray-600 transition">⤢ Expand</button>
                <a id="preview-open-link" href="{{ $firstRelease->previewUrl() }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:underline">Open ↗</a>
            </div>
        </div>
        <div id="preview-container" class="relative w-full bg-gray-100 overflow-hidden transition-all duration-300" style="height: 360px;">
            <iframe
                id="preview-iframe"
                src="{{ $firstRelease->previewUrl() }}"
                class="absolute border-0"
                style="width: 1280px; height: 800px; transform: scale(0.45); transform-origin: top left; left: calc(50% - 288px); top: 0;"
                loading="lazy"
            ></iframe>
        </div>
    </div>

    {{-- Release rows --}}
    <div class="divide-y divide-gray-100 border-t border-gray-100">
        @foreach($sortedReleases as $release)
            @php $isLive = $release->version === $site->live_release; @endphp
            <div class="release-row flex items-center justify-between px-6 py-3 cursor-pointer transition-colors
                    {{ $isLive ? 'bg-emerald-50/50' : 'hover:bg-gray-50' }}"
                data-preview-url="{{ $release->previewUrl() }}"
                data-version="v{{ $release->version }}"
                onclick="selectRelease(this)">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-sm font-bold text-gray-900">v{{ $release->version }}</span>
                    @if($isLive)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>live
                        </span>
                    @elseif($release->version === $site->current_release)
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">staged</span>
                    @endif
                    @if($release->notes)
                        <span class="text-sm text-gray-500 hidden sm:inline">{{ $release->notes }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                    <span class="text-xs text-gray-400">{{ $release->created_at->diffForHumans() }}</span>
                    <a href="{{ route('sites.download.release', [$site, $release]) }}" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-50 transition">Download</a>
                    @if(!$isLive)
                        <form method="POST" action="{{ route('sites.releases.promote', [$site, $release->version]) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">Go Live</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
function selectRelease(row) {
    document.querySelectorAll('.release-row').forEach(function(r) {
        r.classList.remove('bg-brand-50', 'ring-2', 'ring-inset', 'ring-brand-200');
        if (!r.classList.contains('bg-emerald-50/50')) r.classList.remove('bg-brand-50');
    });
    row.classList.add('bg-brand-50');
    var url = row.dataset.previewUrl;
    document.getElementById('preview-iframe').src = url;
    document.getElementById('preview-open-link').href = url;
    document.getElementById('preview-label').textContent = row.dataset.version;
}
function resetPreview() {
    document.getElementById('preview-iframe').src = document.getElementById('preview-iframe').src;
}
function toggleExpand() {
    var c = document.getElementById('preview-container');
    var f = document.getElementById('preview-iframe');
    var btn = document.getElementById('expand-btn');
    if (c.style.height === '360px') {
        c.style.height = '700px';
        f.style.transform = 'scale(0.875)';
        f.style.left = 'calc(50% - 560px)';
        f.style.height = '800px';
        btn.textContent = '⤡ Collapse';
    } else {
        c.style.height = '360px';
        f.style.transform = 'scale(0.45)';
        f.style.left = 'calc(50% - 288px)';
        btn.textContent = '⤢ Expand';
    }
}
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
                <span class="text-emerald-700 font-medium">Release {{ $site->live_release }}</span>
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
@endsection
