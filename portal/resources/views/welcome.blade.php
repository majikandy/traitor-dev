@extends('layouts.app')

@section('title', 'Traitor.dev — Dashboard')

@section('content')
<div class="dashboard">
    <h1>Dashboard</h1>
    <p class="subtitle">Manage your sites.</p>

    <div class="stats">
        <div class="stat-card">
            <span class="stat-number">{{ $total }}</span>
            <span class="stat-label">Sites</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $live }}</span>
            <span class="stat-label">Live</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">{{ $drafts }}</span>
            <span class="stat-label">Drafts</span>
        </div>
    </div>

    <div class="actions">
        <a href="{{ route('sites.create') }}" class="btn btn-primary">Create New Site</a>
        <a href="{{ route('sites.index') }}" class="btn btn-secondary">View All Sites</a>
    </div>
</div>
@endsection
