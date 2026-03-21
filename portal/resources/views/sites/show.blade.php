@extends('layouts.app')

@section('title', $site->name . ' — Traitor.dev')

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $site->name }}</h1>
        <p class="subtitle">
            <span class="badge badge-{{ $site->status }}">{{ $site->status }}</span>
            @if($site->domain)
                <a href="https://{{ $site->domain }}" target="_blank">{{ $site->domain }}</a>
            @endif
        </p>
    </div>
    <div class="header-actions">
        <a href="https://{{ $site->previewUrl() }}" target="_blank" class="btn btn-secondary btn-sm">Preview</a>
    </div>
</div>

{{-- Upload --}}
<div class="card">
    <h2>Upload Files</h2>
    <p class="card-desc">Upload a .zip to replace the current draft.</p>

    <form action="{{ route('sites.upload', $site) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="dropzone" id="dropzone">
            <input type="file" name="zip" accept=".zip" required>
            <p class="dropzone-text">Drag & drop a .zip file here, or click to browse</p>
            <p class="dropzone-filename" id="filename"></p>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 1rem;">Upload</button>
    </form>
</div>

{{-- Publish --}}
<div class="card">
    <h2>Publish</h2>
    <p class="card-desc">Snapshot the current draft and make it live.</p>

    <form action="{{ route('sites.publish', $site) }}" method="POST" class="inline-form">
        @csrf
        <input type="text" name="notes" placeholder="Release notes (optional)" class="inline-input">
        <button type="submit" class="btn btn-primary btn-sm">Publish</button>
    </form>
</div>

{{-- Releases --}}
<div class="card">
    <h2>Releases</h2>

    @if($site->releases->isEmpty())
        <p class="card-desc">No releases yet. Upload files and publish to create the first one.</p>
    @else
        <div class="release-list">
            @foreach($site->releases as $release)
                <div class="release-item {{ $release->version === $site->current_release ? 'release-active' : '' }}">
                    <span class="release-version">
                        v{{ $release->version }}
                        @if($release->version === $site->current_release)
                            <span class="badge badge-live">current</span>
                        @endif
                    </span>
                    <span class="release-notes">{{ $release->notes ?: '—' }}</span>
                    <span class="release-date">{{ $release->created_at->diffForHumans() }}</span>
                    @if($release->version !== $site->current_release)
                        <form action="{{ route('sites.rollback', $site) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Rollback</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Danger Zone --}}
<div class="card card-danger">
    <h2>Danger Zone</h2>
    <form action="{{ route('sites.destroy', $site) }}" method="POST" onsubmit="return confirm('Delete {{ $site->name }}? This cannot be undone.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Delete Site</button>
    </form>
</div>
@endsection
