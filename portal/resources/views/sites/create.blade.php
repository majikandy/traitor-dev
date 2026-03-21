@extends('layouts.app')

@section('title', 'New Site — Traitor.dev')

@section('content')
<h1>New Site</h1>
<p class="subtitle">Give it a name and optionally upload a zip of your site files.</p>

<form action="{{ route('sites.store') }}" method="POST" enctype="multipart/form-data" class="site-form">
    @csrf

    <div class="form-group">
        <label for="name">Site Name</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="My Awesome Site" required autofocus>
        <p class="form-hint">This becomes the slug for your preview URL.</p>
    </div>

    <div class="form-group">
        <label for="zip">Site Files (zip)</label>
        <div class="dropzone" id="dropzone">
            <input type="file" id="zip" name="zip" accept=".zip">
            <p class="dropzone-text">Drag & drop a .zip file here, or click to browse</p>
            <p class="dropzone-filename" id="filename"></p>
        </div>
        <p class="form-hint">Optional — you can upload later. Your zip should contain the files you want served (index.html, css/, etc).</p>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Site</button>
        <a href="{{ route('sites.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection
