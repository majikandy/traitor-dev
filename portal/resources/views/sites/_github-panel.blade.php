@php $ghOpen = $startOpen ?? true; @endphp
<div class="rounded-xl border border-gray-200 bg-white shadow-sm mb-6">
    <button type="button" id="github-toggle"
        class="flex w-full items-center gap-3 px-6 py-4 text-left"
        aria-expanded="{{ $ghOpen ? 'true' : 'false' }}" aria-controls="github-panel">
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-900">
            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.298 24 12c0-6.627-5.373-12-12-12"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <span class="text-base font-semibold text-gray-900">GitHub</span>
            @if($site->github_repo)
                <span class="ml-2 text-sm text-gray-400 font-normal truncate">{{ $site->github_repo }}</span>
            @endif
        </div>
        <svg id="github-chevron" class="h-4 w-4 text-gray-400 transition-transform duration-200{{ $ghOpen ? ' rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div id="github-panel" class="{{ $ghOpen ? '' : 'hidden' }} px-6 pb-6 sm:pl-[4.5rem]">
        @if($site->github_repo)
            {{-- State 3: Repo connected --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Connected
                    </span>
                    <span class="text-sm font-medium text-gray-900">{{ $site->github_repo }}</span>
                    @if($site->github_repo_path)
                        <span class="text-sm text-gray-400">/ {{ $site->github_repo_path }}</span>
                    @endif
                    <a href="{{ $site->organisation->githubInstallationUrl() }}" target="_blank" rel="noopener"
                       class="text-xs text-gray-400 hover:text-gray-600 transition">Manage repo access ↗</a>
                </div>
                <p class="text-sm text-gray-500">
                    Pushes to <span class="font-medium text-gray-700">{{ $site->github_branch ?? 'default branch' }}</span> create a new release automatically.
                    @if($site->github_repo_path) Only changes inside <code class="text-xs bg-gray-100 px-1 rounded">{{ $site->github_repo_path }}</code> trigger a release. @endif
                </p>

                {{-- Auto-deploy toggle --}}
                <form method="POST" action="{{ route('github.auto-deploy', $site) }}" class="flex items-center gap-3">
                    @csrf
                    <button type="submit" class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 {{ $site->github_auto_deploy ? 'bg-brand-600' : 'bg-gray-200' }}">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 {{ $site->github_auto_deploy ? 'translate-x-4' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-sm text-gray-700">Auto make current <span class="text-gray-400">(promote straight to current on push)</span></span>
                </form>

                <div class="flex items-center gap-4 pt-1">
                    <a href="{{ route('github.select-repo-form', $site) }}" class="text-xs text-gray-400 hover:text-gray-600 transition">Change repository</a>
                    <form method="POST" action="{{ route('github.disconnect', $site) }}" data-confirm="Disconnect {{ $site->github_repo }}? Auto-deploys will stop.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition">Disconnect</button>
                    </form>
                </div>
            </div>
        @elseif($site->organisation->hasGitHub())
            {{-- State 2: Org has GitHub app installed, but this site has no repo yet --}}
            <div class="space-y-3">
                <p class="text-sm text-gray-500">GitHub is connected to your organisation. Choose a repository for this site.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('github.select-repo-form', $site) }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.298 24 12c0-6.627-5.373-12-12-12"/></svg>
                        Select repository
                    </a>
                    <a href="{{ $site->organisation->githubInstallationUrl() }}" target="_blank" rel="noopener"
                       class="text-xs text-gray-500 hover:text-gray-700 transition">Add repo access ↗</a>
                </div>
            </div>
        @else
            {{-- State 1: No GitHub connection at all --}}
            <p class="text-sm text-gray-500 mb-4">Connect a GitHub repository to auto-create releases on push to the default branch.</p>
            <a href="{{ route('github.install', $site) }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.298 24 12c0-6.627-5.373-12-12-12"/></svg>
                Connect GitHub
            </a>
        @endif
    </div>
</div>
<script>
    (function () {
        var btn = document.getElementById('github-toggle');
        var panel = document.getElementById('github-panel');
        var chevron = document.getElementById('github-chevron');
        btn.addEventListener('click', function () {
            var hidden = panel.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', String(!hidden));
            chevron.style.transform = hidden ? '' : 'rotate(180deg)';
        });
    })();
</script>
