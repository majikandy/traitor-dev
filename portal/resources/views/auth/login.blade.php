<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Traitor.dev</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { 600: '#2563eb', 700: '#1d4ed8' } } } }
        }
    </script>
</head>
<body class="h-full bg-gray-50 flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" class="h-12 w-12 rounded-xl mb-4 mx-auto">
                <defs>
                    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#0f172a"/>
                        <stop offset="100%" style="stop-color:#1e293b"/>
                    </linearGradient>
                    <linearGradient id="slash" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#818cf8"/>
                        <stop offset="100%" style="stop-color:#6366f1"/>
                    </linearGradient>
                </defs>
                <rect width="200" height="200" rx="40" fill="url(#bg)"/>
                <polyline points="62,68 38,100 62,132" fill="none" stroke="white" stroke-width="13" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="138,68 162,100 138,132" fill="none" stroke="white" stroke-width="13" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="118" y1="60" x2="82" y2="140" stroke="url(#slash)" stroke-width="13" stroke-linecap="round"/>
            </svg>
            <h1 class="text-2xl font-bold text-gray-900">traitor<span class="text-blue-600">.dev</span></h1>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
            @if(session('status'))
                <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Passkey view (default) --}}
            <div id="passkey-view">
                <p class="text-sm text-gray-500 text-center mb-6">Sign in securely with your device.</p>

                <button onclick="loginWithPasskey()" id="passkey-btn"
                    class="w-full flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                    </svg>
                    Sign in with passkey
                </button>
                <p id="passkey-error" class="mt-2 text-xs text-red-600 text-center hidden"></p>

                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-400">or</span></div>
                </div>

                <button onclick="showPasswordView()"
                    class="w-full text-sm text-gray-500 hover:text-gray-700 transition text-center">
                    Sign in with password
                </button>
            </div>

            {{-- Password view (hidden by default) --}}
            <div id="password-view" class="hidden">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" autofocus required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        Sign in
                    </button>
                </form>

                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-400">or</span></div>
                </div>

                <button onclick="showPasskeyView()"
                    class="w-full flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                    </svg>
                    Use passkey instead
                </button>

                <p class="mt-4 text-center text-xs text-gray-400">
                    <a href="{{ route('password.request') }}" class="hover:text-gray-600 underline">Forgot password?</a>
                </p>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">
            New to traitor.dev? <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-medium">Create an account</a>
        </p>
    </div>

<script>
function showPasswordView() {
    document.getElementById('passkey-view').classList.add('hidden');
    document.getElementById('password-view').classList.remove('hidden');
    document.getElementById('email').focus();
}

function showPasskeyView() {
    document.getElementById('password-view').classList.add('hidden');
    document.getElementById('passkey-view').classList.remove('hidden');
}

// If there were validation errors, show the password form
@if($errors->any() || old('email'))
    showPasswordView();
@endif

async function loginWithPasskey() {
    const btn = document.getElementById('passkey-btn');
    const err = document.getElementById('passkey-error');
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Waiting…';
    err.className = 'mt-2 text-xs text-red-600 text-center hidden';

    try {
        const optRes = await fetch('{{ route('passkeys.auth-options') }}');
        const options = await optRes.json();

        options.challenge = base64urlToBuffer(options.challenge);
        if (options.allowCredentials) {
            options.allowCredentials = options.allowCredentials.map(c => ({ ...c, id: base64urlToBuffer(c.id) }));
        }

        const credential = await navigator.credentials.get({ publicKey: options });

        const body = {
            id: credential.id,
            rawId: bufferToBase64url(credential.rawId),
            type: credential.type,
            response: {
                authenticatorData: bufferToBase64url(credential.response.authenticatorData),
                clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                signature: bufferToBase64url(credential.response.signature),
                userHandle: credential.response.userHandle ? bufferToBase64url(credential.response.userHandle) : null,
            }
        };

        const res = await fetch('{{ route('passkeys.authenticate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(body),
        });

        if (!res.ok) throw new Error('Authentication failed. Try signing in with your password.');

        window.location.href = '/';
    } catch (e) {
        if (e.name === 'NotAllowedError') {
            err.textContent = 'Passkey sign-in was cancelled.';
        } else {
            err.textContent = e.message || 'Passkey sign-in failed.';
        }
        err.className = 'mt-2 text-xs text-red-600 text-center';
        btn.disabled = false;
        btn.innerHTML = origHTML;
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
</body>
</html>
