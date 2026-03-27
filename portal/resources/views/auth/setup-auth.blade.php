<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure your account — Traitor.dev</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { 600: '#2563eb', 700: '#1d4ed8' } } } }
        }
    </script>
</head>
<body class="h-full bg-gray-50 flex items-center justify-center py-10">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 mb-4">
                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Secure your account</h1>
            <p class="mt-1 text-sm text-gray-500">Add a passkey or password to continue.</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            @if(session('error') || $errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ session('error') ?: $errors->first() }}
                </div>
            @endif

            <div id="error-box" class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 hidden"></div>

            {{-- Passkey (default) --}}
            <div id="passkey-view">
                <p class="text-sm text-gray-500 text-center mb-6">
                    Passkeys are the most secure option — no password to forget or lose.
                </p>

                <button type="button" onclick="setupPasskey()" id="passkey-btn"
                    class="w-full flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                    </svg>
                    Set up passkey
                </button>

                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-400">or</span></div>
                </div>

                <button type="button" onclick="showPasswordView()"
                    class="w-full text-sm text-gray-500 hover:text-gray-700 transition text-center">
                    Set a password instead
                </button>
            </div>

            {{-- Password option --}}
            <div id="password-view" class="hidden">
                <form method="POST" action="{{ route('setup-auth.save') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" required autofocus minlength="8"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        Set password
                    </button>
                </form>

                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-400">or</span></div>
                </div>

                <button type="button" onclick="showPasskeyView()"
                    class="w-full flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                    </svg>
                    Use passkey instead
                </button>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="hover:text-gray-600 underline">Sign out</button>
            </form>
        </p>
    </div>

<script>
function showPasswordView() {
    document.getElementById('passkey-view').classList.add('hidden');
    document.getElementById('password-view').classList.remove('hidden');
}

function showPasskeyView() {
    document.getElementById('password-view').classList.add('hidden');
    document.getElementById('passkey-view').classList.remove('hidden');
}

function showError(msg) {
    var box = document.getElementById('error-box');
    box.textContent = msg;
    box.classList.remove('hidden');
}

async function setupPasskey() {
    var btn = document.getElementById('passkey-btn');
    var origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Waiting…';

    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        var optRes = await fetch('{{ route('passkeys.register-options') }}');
        var options = await optRes.json();

        options.challenge = base64urlToBuffer(options.challenge);
        options.user.id   = base64urlToBuffer(options.user.id);
        if (options.excludeCredentials) {
            options.excludeCredentials = options.excludeCredentials.map(c => ({ ...c, id: base64urlToBuffer(c.id) }));
        }

        var credential = await navigator.credentials.create({ publicKey: options });

        var saveRes = await fetch('{{ route('passkeys.register') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                id:       credential.id,
                rawId:    bufferToBase64url(credential.rawId),
                type:     credential.type,
                response: {
                    attestationObject: bufferToBase64url(credential.response.attestationObject),
                    clientDataJSON:    bufferToBase64url(credential.response.clientDataJSON),
                },
            }),
        });

        if (!saveRes.ok) throw new Error('Passkey could not be saved. Please try again.');

        window.location.href = '/';

    } catch (e) {
        if (e.name === 'NotAllowedError') {
            showError('Passkey setup was cancelled. Try again or set a password instead.');
        } else {
            showError(e.message || 'Something went wrong. Please try again.');
        }
        btn.disabled = false;
        btn.innerHTML = origHTML;
    }
}

function base64urlToBuffer(base64url) {
    if (base64url instanceof ArrayBuffer) return base64url;
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
