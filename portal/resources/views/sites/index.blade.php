@extends('layouts.app')

@section('title', 'Sites — Traitor.dev')

@section('content')
<div class="page-header">
    <div>
        <h1>Sites</h1>
        <p class="subtitle">All your sites in one place.</p>
    </div>
    <a href="{{ route('sites.create') }}" class="btn btn-primary">New Site</a>
</div>

@if($sites->isEmpty())
    <div class="empty-state">
        <p>No sites yet.</p>
        <a href="{{ route('sites.create') }}" class="btn btn-primary">Create your first site</a>
    </div>
@else
    <div class="site-list">
        @foreach($sites as $site)
            <a href="{{ route('sites.show', $site) }}" class="site-card">
                <div>
                    <h3>{{ $site->name }}</h3>
                    <span class="site-domain">{{ $site->slug }}.sites.traitor.dev</span>
                </div>
                <div class="site-meta">
                    <span class="badge badge-{{ $site->status }}">{{ $site->status }}</span>
                    @if($site->current_release)
                        <span class="release-tag">v{{ $site->current_release }}</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
