<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite no longer valid — Traitor.dev</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-50 flex items-center justify-center py-10">
    <div class="w-full max-w-sm text-center">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 mb-4">
            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">traitor<span class="text-blue-600">.dev</span></h1>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 mt-6">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">This invite is no longer valid</h2>
            <p class="text-sm text-gray-500">The invitation link has been cancelled or already used. Ask the person who invited you to send a fresh one.</p>
        </div>

        <p class="mt-6 text-xs text-gray-400">
            Already have an account? <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Sign in</a>
        </p>
    </div>
</body>
</html>
