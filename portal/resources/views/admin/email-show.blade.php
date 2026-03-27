@extends('layouts.app')

@section('title', 'Email — Traitor.dev')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <span class="text-gray-400">Admin</span>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('admin.emails') }}" class="hover:text-gray-700">Sent Emails</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium truncate max-w-xs">{{ $email->subject }}</span>
</nav>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Meta --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-xs font-medium text-gray-500 mb-1">To</dt>
                <dd class="text-gray-900">{{ $email->to }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 mb-1">Subject</dt>
                <dd class="text-gray-900">{{ $email->subject }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 mb-1">Sent</dt>
                <dd class="text-gray-900" title="{{ $email->created_at->format('d M Y H:i:s') }}">{{ $email->created_at->diffForHumans() }}</dd>
            </div>
        </dl>
    </div>

    {{-- HTML body --}}
    @if($email->body_html)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 bg-gray-50">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">HTML</span>
                <button onclick="toggleRaw()" class="text-xs text-brand-600 hover:text-brand-700 font-medium transition" id="raw-toggle">View source</button>
            </div>
            <iframe id="email-frame" srcdoc="{{ $email->body_html }}"
                class="w-full border-0 bg-white"
                style="min-height:500px;"
                onload="this.style.height = this.contentDocument.body.scrollHeight + 'px'">
            </iframe>
            <pre id="email-raw" class="hidden p-6 text-xs text-gray-700 overflow-x-auto whitespace-pre-wrap font-mono">{{ $email->body_html }}</pre>
        </div>
    @endif

    {{-- Plain text body --}}
    @if($email->body_text)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Plain text</span>
            </div>
            <pre class="p-6 text-sm text-gray-700 whitespace-pre-wrap font-mono">{{ $email->body_text }}</pre>
        </div>
    @endif
</div>

<script>
function toggleRaw() {
    var frame = document.getElementById('email-frame');
    var raw   = document.getElementById('email-raw');
    var btn   = document.getElementById('raw-toggle');
    if (raw.classList.contains('hidden')) {
        raw.classList.remove('hidden');
        frame.classList.add('hidden');
        btn.textContent = 'View rendered';
    } else {
        raw.classList.add('hidden');
        frame.classList.remove('hidden');
        btn.textContent = 'View source';
    }
}
</script>
@endsection
