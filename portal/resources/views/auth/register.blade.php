<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create account — Traitor.dev</title>
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
                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">traitor<span class="text-blue-600">.dev</span></h1>
            <p class="mt-1 text-sm text-gray-500">Create your account</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            @if($errors->any())
                <div id="error-box" class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @else
                <div id="error-box" class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 hidden"></div>
            @endif

            {{-- Passkey form: shared fields + passkey button. Enter submits passkey flow. --}}
            <form id="passkey-form" onsubmit="event.preventDefault(); registerWithPasskey();">
            <div class="space-y-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Organisation / business name</label>
                    <input id="f-org" type="text" name="organisation" value="{{ old('organisation') }}"
                        placeholder="Acme Ltd" autofocus
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Your name</label>
                    <input id="f-name" type="text" name="name" value="{{ old('name') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input id="f-email" type="email" name="email" value="{{ old('email') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            {{-- Passkey option (default) --}}
            <div id="passkey-view">
                <button type="submit" id="passkey-btn"
                    class="w-full flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                    </svg>
                    Create account with passkey
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
            </form>

            {{-- Password option (hidden by default) --}}
            <div id="password-view" class="hidden">
                <form method="POST" action="{{ route('register') }}" id="password-form">
                    @csrf
                    <input type="hidden" name="organisation" id="p-org">
                    <input type="hidden" name="name" id="p-name">
                    <input type="hidden" name="email" id="p-email">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" required minlength="8"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        Create account
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
            Already have an account? <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Sign in</a>
        </p>
    </div>

@if($errors->any())
<script>showPasswordView();</script>
@endif

<script>
function showPasswordView() {
    // Sync shared fields into password form hidden inputs
    document.getElementById('p-org').value   = document.getElementById('f-org').value;
    document.getElementById('p-name').value  = document.getElementById('f-name').value;
    document.getElementById('p-email').value = document.getElementById('f-email').value;

    document.getElementById('passkey-view').classList.add('hidden');
    document.getElementById('password-view').classList.remove('hidden');
}

function showPasskeyView() {
    document.getElementById('password-view').classList.add('hidden');
    document.getElementById('passkey-view').classList.remove('hidden');
}

// Keep hidden password form fields in sync as user types
['f-org', 'f-name', 'f-email'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', function() {
        var map = {'f-org': 'p-org', 'f-name': 'p-name', 'f-email': 'p-email'};
        document.getElementById(map[id]).value = this.value;
    });
});

function showError(msg) {
    var box = document.getElementById('error-box');
    box.textContent = msg;
    box.classList.remove('hidden');
}

async function registerWithPasskey() {
    var org   = document.getElementById('f-org').value.trim();
    var name  = document.getElementById('f-name').value.trim();
    var email = document.getElementById('f-email').value.trim();
    var accountCreated = false;

    if (!org || !name || !email) {
        showError('Please fill in all fields before continuing.');
        return;
    }

    var btn = document.getElementById('passkey-btn');
    var origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Setting up…';

    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        // Step 1: create org+user, get creation options
        var startRes = await fetch('{{ route('register.passkey-start') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ organisation: org, name: name, email: email }),
        });

        if (!startRes.ok) {
            var err = await startRes.json().catch(() => ({}));
            throw new Error(err.message || (err.errors ? Object.values(err.errors).flat().join(' ') : 'Could not create account. Email may already be registered.'));
        }

        accountCreated = true;
        var options = await startRes.json();
        options.challenge = base64urlToBuffer(options.challenge);
        options.user.id   = base64urlToBuffer(options.user.id);
        if (options.excludeCredentials) {
            options.excludeCredentials = options.excludeCredentials.map(c => ({ ...c, id: base64urlToBuffer(c.id) }));
        }

        // Step 2: browser passkey prompt
        var credential = await navigator.credentials.create({ publicKey: options });

        // Step 3: save credential
        var saveRes = await fetch('{{ route('passkeys.register') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                id:       credential.id,
                rawId:    bufferToBase64url(credential.rawId),
                type:     credential.type,
                name:     name,
                response: {
                    attestationObject: bufferToBase64url(credential.response.attestationObject),
                    clientDataJSON:    bufferToBase64url(credential.response.clientDataJSON),
                },
            }),
        });

        if (!saveRes.ok) throw new Error('Passkey could not be saved. Please try again.');

        window.location.href = '/';

    } catch (e) {
        if (accountCreated) {
            // Account exists but passkey failed/cancelled — send to /setup
            // where they must complete the auth method before proceeding.
            window.location.href = '{{ route('setup-auth') }}';
            return;
        }

        if (e.name === 'NotAllowedError') {
            showError('Passkey setup was cancelled. Try again or use a password.');
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
