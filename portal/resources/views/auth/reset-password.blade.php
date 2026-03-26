<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set up your account — Traitor.dev</title>
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
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 mb-4">
                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">traitor<span class="text-blue-600">.dev</span></h1>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Set up your account</h2>
            <p class="text-sm text-gray-500 mb-5">Choose how you want to sign in.</p>

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Passkey option --}}
            <button onclick="setupPasskey()" id="passkey-btn"
                class="w-full flex items-center gap-3 rounded-lg border-2 border-blue-600 px-4 py-3 text-sm font-medium text-blue-700 hover:bg-blue-50 transition mb-3">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                </svg>
                <span>Use passkey (Touch ID / Face ID)</span>
            </button>
            <p id="passkey-error" class="mb-3 text-xs text-red-600 hidden"></p>

            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-400">or set a password</span></div>
            </div>

            {{-- Password form --}}
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="email-field" value="{{ old('email', $email ?? '') }}" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required autofocus
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <button type="submit"
                    class="w-full rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-900 transition">
                    Set password &amp; sign in
                </button>
            </form>
        </div>
    </div>

<script>
const TOKEN = '{{ $token }}';
const INVITE_OPTIONS_URL = '{{ route('passkeys.invite-options') }}';
const REGISTER_URL = '{{ route('passkeys.register') }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function setupPasskey() {
    const btn = document.getElementById('passkey-btn');
    const err = document.getElementById('passkey-error');
    const email = document.getElementById('email-field').value;
    err.className = 'mb-3 text-xs text-red-600 hidden';

    if (!email) {
        err.textContent = 'Please enter your email address first.';
        err.className = 'mb-3 text-xs text-red-600';
        return;
    }

    btn.disabled = true;
    btn.querySelector('span').textContent = 'Waiting for device…';

    try {
        // Step 1: validate token, log in, get creation options
        const optRes = await fetch(INVITE_OPTIONS_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ token: TOKEN, email }),
        });

        if (!optRes.ok) {
            const data = await optRes.json();
            throw new Error(data.error || 'Invalid or expired link.');
        }

        const options = await optRes.json();
        options.challenge = base64urlToBuffer(options.challenge);
        options.user.id = base64urlToBuffer(options.user.id);
        if (options.excludeCredentials) {
            options.excludeCredentials = options.excludeCredentials.map(c => ({ ...c, id: base64urlToBuffer(c.id) }));
        }

        // Step 2: browser creates the passkey
        const credential = await navigator.credentials.create({ publicKey: options });

        // Step 3: register it (session is now authenticated from step 1)
        const body = {
            name: detectPasskeyName(),
            id: credential.id,
            rawId: bufferToBase64url(credential.rawId),
            type: credential.type,
            response: {
                attestationObject: bufferToBase64url(credential.response.attestationObject),
                clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
            },
        };

        const regRes = await fetch(REGISTER_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body),
        });

        if (!regRes.ok) throw new Error('Failed to save passkey.');

        window.location.href = '/';
    } catch (e) {
        err.textContent = e.name === 'NotAllowedError' ? 'Cancelled.' : (e.message || 'Something went wrong.');
        err.className = 'mb-3 text-xs text-red-600';
        btn.disabled = false;
        btn.querySelector('span').textContent = 'Use passkey (Touch ID / Face ID)';
    }
}

function detectPasskeyName() {
    const ua = navigator.userAgent;
    let device, browser;

    if (/iPhone/.test(ua))           device = 'iPhone';
    else if (/iPad/.test(ua))        device = 'iPad';
    else if (/Android/.test(ua))     device = 'Android';
    else if (/Macintosh/.test(ua))   device = 'Mac';
    else if (/Windows/.test(ua))     device = 'Windows';
    else                             device = 'Device';

    if (/iPhone|iPad|Android/.test(ua)) return device;

    if (/Edg\//.test(ua))            browser = 'Edge';
    else if (/OPR\//.test(ua))       browser = 'Opera';
    else if (/Chrome\//.test(ua))    browser = 'Chrome';
    else if (/Firefox\//.test(ua))   browser = 'Firefox';
    else if (/Safari\//.test(ua))    browser = 'Safari';
    else                             browser = 'Browser';

    return browser + ' on ' + device;
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
