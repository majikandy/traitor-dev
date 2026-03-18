@extends('layouts.app')

@section('title', 'Traitor.dev — Dashboard')

@section('content')
<div class="dashboard">
    <h1>Dashboard</h1>
    <p class="subtitle">Manage your sites.</p>

    <div class="stats">
        <div class="stat-card">
            <span class="stat-number">0</span>
            <span class="stat-label">Sites</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">0</span>
            <span class="stat-label">Live</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">0</span>
            <span class="stat-label">Drafts</span>
        </div>
    </div>

    <div class="actions">
        <a href="/sites/create" class="btn btn-primary">Create New Site</a>
    </div>
</div>
@endsection
