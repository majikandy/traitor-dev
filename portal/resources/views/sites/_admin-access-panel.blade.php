<div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6">
    <div class="flex w-full items-center gap-3 px-6 py-4">
        <button type="button" id="admin-access-toggle"
            class="flex flex-1 items-center gap-3 text-left min-w-0"
            aria-expanded="false" aria-controls="admin-access-panel">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100">
                <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" /></svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-base font-semibold text-gray-900">Admin Access</span>
                @if($site->admin_url)
                    <span class="ml-2 text-sm text-gray-400 font-normal truncate">{{ $site->admin_url }}</span>
                @endif
            </div>
            <svg id="admin-access-chevron" class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
        @if($site->admin_url)
            <a href="{{ route('sites.admin.open', $site) }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-700 transition flex-shrink-0">
                Open Admin ↗
            </a>
        @endif
    </div>

    <div id="admin-access-panel" class="hidden px-6 pb-6 sm:pl-[4.5rem]">
        @if($site->admin_url)
            {{-- Connected --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Configured
                    </span>
                    <span class="text-sm text-gray-600 font-mono">{{ $site->admin_url }}</span>
                </div>
                <p class="text-sm text-gray-500">
                    Clicking "Open Admin" generates a 30-second HMAC token and redirects directly in. The secret never leaves the server.
                </p>
                <div class="flex items-center gap-4 pt-1">
                    <button type="button" onclick="document.getElementById('admin-reconfigure').classList.toggle('hidden')"
                        class="text-xs text-gray-400 hover:text-gray-600 transition">Reconfigure</button>
                    <form method="POST" action="{{ route('sites.admin-access.remove', $site) }}" data-confirm="Remove admin access for {{ $site->name }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition">Remove</button>
                    </form>
                </div>
                <div id="admin-reconfigure" class="hidden">
                    @include('sites._admin-access-form')
                </div>
            </div>
        @else
            {{-- Setup --}}
            <div class="space-y-5">
                <p class="text-sm text-gray-500">
                    Set up one-click access to your app's admin area using a shared HMAC secret.
                    The portal generates a short-lived token (valid ~60 seconds) — no passwords in URLs.
                </p>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Step 1 — Add to your app's <code class="font-mono normal-case bg-gray-100 px-1 rounded">.env</code></p>
                    <div class="relative">
                        <pre class="text-xs font-mono text-gray-700 bg-white border border-gray-200 rounded px-3 py-2 pr-16">ADMIN_SHARED_SECRET=your-secret-here</pre>
                        <button onclick="navigator.clipboard.writeText('ADMIN_SHARED_SECRET=your-secret-here').then(()=>{this.textContent='✓';setTimeout(()=>this.textContent='Copy',1200)})"
                            class="absolute right-2 top-1.5 text-xs text-gray-400 hover:text-gray-600 border border-gray-200 rounded px-1.5 py-0.5 bg-white">Copy</button>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Step 2 — Validate in your app's admin middleware</p>
                    @php
$middlewareCode = <<<'CODE'
public function handle(Request $request, Closure $next)
{
    $token  = $request->query('token');
    $secret = env('ADMIN_SHARED_SECRET');
    $window = (int) floor(time() / 30);

    $valid = $token && $secret && (
        hash_equals(hash_hmac('sha256', $window,     $secret), $token) ||
        hash_equals(hash_hmac('sha256', $window - 1, $secret), $token)
    );

    if ($valid) {
        // Token is good — log in and strip it from the URL
        Auth::loginUsingId(1);
        return redirect($request->fullUrlWithoutQuery(['token']));
    }

    // Fall through to normal auth check
    if (!Auth::check()) {
        return redirect('/admin/login');
    }

    return $next($request);
}
CODE;
                    @endphp
                    <div class="relative">
                        <pre class="text-xs font-mono text-gray-700 bg-white border border-gray-200 rounded px-3 py-2 pr-16 overflow-x-auto">{{ $middlewareCode }}</pre>
                        <button onclick="navigator.clipboard.writeText(this.closest('.relative').querySelector('pre').textContent).then(()=>{this.textContent='✓';setTimeout(()=>this.textContent='Copy',1200)})"
                            class="absolute right-2 top-1.5 text-xs text-gray-400 hover:text-gray-600 border border-gray-200 rounded px-1.5 py-0.5 bg-white">Copy</button>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Step 3 — Configure here</p>
                    @include('sites._admin-access-form')
                </div>
            </div>
        @endif
    </div>
</div>
<script>
(function () {
    var btn     = document.getElementById('admin-access-toggle');
    var panel   = document.getElementById('admin-access-panel');
    var chevron = document.getElementById('admin-access-chevron');
    btn.addEventListener('click', function () {
        var hidden = panel.classList.toggle('hidden');
        btn.setAttribute('aria-expanded', String(!hidden));
        chevron.style.transform = hidden ? '' : 'rotate(180deg)';
    });
})();
</script>
