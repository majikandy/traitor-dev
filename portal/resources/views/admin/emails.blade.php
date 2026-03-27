@extends('layouts.app')

@section('title', 'Sent Emails — Traitor.dev')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <span class="text-gray-400">Admin</span>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">Sent Emails</span>
</nav>
@endsection

@section('content')
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-gray-500">{{ $emails->total() }} emails sent. Newest first.</p>
</div>

@if($emails->isEmpty())
    <div class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400">
        No emails sent yet.
    </div>
@else
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-100">
            @foreach($emails as $email)
                <a href="{{ route('admin.emails.show', $email) }}"
                   class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $email->subject }}</p>
                        <p class="text-xs text-gray-400 truncate mt-0.5">To: {{ $email->to }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xs text-gray-400" title="{{ $email->created_at->format('d M Y H:i:s') }}">{{ $email->created_at->diffForHumans() }}</p>
                    </div>
                    <svg class="h-4 w-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        {{ $emails->links() }}
    </div>
@endif
@endsection
