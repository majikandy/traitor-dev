@extends('layouts.app')

@section('title', 'Profile — Traitor.dev')
@section('page-title', 'Profile')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">Profile</span>
</nav>
@endsection

@section('content')
<div class="max-w-lg space-y-6">

    {{-- Change name --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Your details</h2>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                <input type="text" name="name" value="{{ auth()->user()->name }}" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ auth()->user()->email }}" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
                Save
            </button>
        </form>
    </div>

    {{-- Change password --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Change password</h2>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Current password</label>
                <input type="password" name="current_password" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
                Update password
            </button>
        </form>
    </div>

    {{-- Passkeys --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Passkeys</h2>
                <p class="text-xs text-gray-500 mt-0.5">Sign in with Touch ID, Face ID, or your device PIN — no password needed.</p>
            </div>
            <button id="add-passkey-btn" onclick="registerPasskey()"
                class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700 transition">
                Add passkey
            </button>
        </div>

        @php $passkeys = auth()->user()->passkeys()->latest()->get(); @endphp

        @if($passkeys->isEmpty())
            <p class="text-sm text-gray-400 italic">No passkeys registered yet.</p>
        @else
            <ul class="space-y-2">
                @foreach($passkeys as $passkey)
                    <li class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2.5">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $passkey->name }}</p>
                                <p class="text-xs text-gray-400">Added {{ $passkey->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('passkeys.destroy', $passkey) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition"
                                onclick="return confirm('Remove this passkey?')">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        <p id="passkey-status" class="mt-3 text-sm hidden"></p>
    </div>

</div>

<script>
async function registerPasskey() {
    const btn = document.getElementById('add-passkey-btn');
    const status = document.getElementById('passkey-status');
    btn.disabled = true;
    btn.textContent = 'Waiting for device…';
    status.className = 'mt-3 text-sm hidden';

    try {
        const optRes = await fetch('{{ route('passkeys.register-options') }}', {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const options = await optRes.json();

        options.challenge = base64urlToBuffer(options.challenge);
        options.user.id = base64urlToBuffer(options.user.id);
        if (options.excludeCredentials) {
            options.excludeCredentials = options.excludeCredentials.map(c => ({ ...c, id: base64urlToBuffer(c.id) }));
        }

        const credential = await navigator.credentials.create({ publicKey: options });

        const body = {
            name: 'Passkey',
            id: credential.id,
            rawId: bufferToBase64url(credential.rawId),
            type: credential.type,
            response: {
                attestationObject: bufferToBase64url(credential.response.attestationObject),
                clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
            }
        };

        const res = await fetch('{{ route('passkeys.register') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(body),
        });

        if (!res.ok) throw new Error(await res.text());

        window.location.reload();
    } catch (e) {
        status.textContent = e.message || 'Passkey registration failed.';
        status.className = 'mt-3 text-sm text-red-600';
        btn.disabled = false;
        btn.textContent = 'Add passkey';
    }
}

function base64urlToBuffer(base64url) {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const binary = atob(base64);
    const buf = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) buf[i] = binary.charCodeAt(i);
    return buf.buffer;
}

function bufferToBase64url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (const byte of bytes) binary += String.fromCharCode(byte);
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
}
</script>
@endsection
