<form method="POST" action="{{ route('sites.admin-access.save', $site) }}" class="space-y-3">
    @csrf
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Admin URL</label>
        <input type="url" name="admin_url" placeholder="https://yoursite.com/admin"
            value="{{ old('admin_url', $site->admin_url) }}"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
        <p class="mt-1 text-xs text-gray-400">The URL the token will be appended to as <code class="bg-gray-100 px-1 rounded">?token=…</code></p>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Shared Secret <span class="font-normal text-gray-400">(matches <code class="bg-gray-100 px-1 rounded">ADMIN_SHARED_SECRET</code> in your app)</span></label>
        <div class="flex gap-2">
            <input type="text" name="admin_secret" id="admin-secret-input" placeholder="min 16 characters"
                value="{{ old('admin_secret', $site->admin_token_secret) }}"
                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            <button type="button" onclick="(function(){var s=Array.from(crypto.getRandomValues(new Uint8Array(24))).map(b=>b.toString(16).padStart(2,'0')).join('');document.getElementById('admin-secret-input').value=s;})()"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 transition whitespace-nowrap">Generate</button>
        </div>
    </div>
    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">Save</button>
</form>
