@extends('layouts.app')

@section('title', 'Connect GitHub Repository — ' . $site->name)
@section('page-title', 'Connect GitHub Repository')

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm text-gray-500">
    <a href="/" class="hover:text-gray-700">Dashboard</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('sites.index') }}" class="hover:text-gray-700">Sites</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('sites.show', $site) }}" class="hover:text-gray-700">{{ $site->name }}</a>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="text-gray-900 font-medium">GitHub</span>
</nav>
@endsection

@section('content')
<div class="max-w-lg">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900">
                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.298 24 12c0-6.627-5.373-12-12-12"/></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Select a repository</h2>
                <p class="text-sm text-gray-500">Choose which repo to connect to <span class="font-medium">{{ $site->name }}</span></p>
            </div>
        </div>

        @if(count($repos) === 0)
            <p class="text-sm text-gray-500 mb-4">No repositories found. Grant access to at least one repository in the GitHub App settings, then come back.</p>
            <div class="flex items-center gap-4">
                <a href="{{ $site->organisation->githubInstallationUrl() }}" target="_blank" rel="noopener"
                   class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">
                    Add repo access on GitHub ↗
                </a>
                <a href="{{ route('github.select-repo-form', $site) }}" class="text-sm text-gray-500 hover:text-gray-700 transition">Refresh</a>
            </div>
        @else
            <form method="POST" action="{{ route('github.select-repo', $site) }}" id="repo-form">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Repository</label>
                    <select name="repo" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">— choose a repository —</option>
                        @foreach($repos as $repo)
                            <option value="{{ $repo }}">{{ $repo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Subfolder <span class="text-gray-400 font-normal">(optional — for monorepos)</span></label>
                    <input type="text" name="repo_path" id="repo-path" placeholder="e.g. sites/my-site" autocomplete="off"
                        list="dirs-list"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <datalist id="dirs-list"></datalist>
                    <p class="mt-1 text-xs text-gray-400" id="dirs-hint">Select a repository above to browse its folders.</p>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Branch</label>
                    <p class="text-sm text-gray-500" id="branch-default-label">Select a repository to see its default branch.</p>
                    <input type="text" name="branch" id="branch-input" placeholder="e.g. production"
                        class="hidden w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg id="submit-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <span id="submit-label">Connect &amp; import</span>
                    </button>
                    <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
            <script>
                document.getElementById('repo-form').addEventListener('submit', function () {
                    var btn = document.getElementById('submit-btn');
                    var spinner = document.getElementById('submit-spinner');
                    var label = document.getElementById('submit-label');
                    btn.disabled = true;
                    spinner.classList.remove('hidden');
                    label.textContent = 'Importing…';
                });

                (function () {
                    var defaultBranches = @json($defaultBranches);
                    var repoSelect      = document.querySelector('select[name="repo"]');
                    var datalist        = document.getElementById('dirs-list');
                    var hint            = document.getElementById('dirs-hint');
                    var branchLabel     = document.getElementById('branch-default-label');
                    var branchInput     = document.getElementById('branch-input');
                    var dirsUrl         = '{{ route('github.repo-dirs', $site) }}';

                    repoSelect.addEventListener('change', function () {
                        var repo = this.value;
                        datalist.innerHTML = '';

                        // Branch UI
                        if (repo && defaultBranches[repo]) {
                            var def = defaultBranches[repo];
                            branchInput.classList.add('hidden');
                            branchInput.value = '';
                            branchLabel.innerHTML = '<span class="font-medium text-gray-700">' + def + '</span>'
                                + ' &mdash; <button type="button" id="branch-override" class="text-xs text-brand-600 hover:underline">Use a different branch</button>';
                            document.getElementById('branch-override').addEventListener('click', function () {
                                branchLabel.classList.add('hidden');
                                branchInput.classList.remove('hidden');
                                branchInput.placeholder = def;
                                branchInput.focus();
                            });
                        } else {
                            branchLabel.textContent = 'Select a repository to see its default branch.';
                            branchLabel.classList.remove('hidden');
                            branchInput.classList.add('hidden');
                            branchInput.value = '';
                        }

                        // Folder picker
                        if (!repo) {
                            hint.textContent = 'Select a repository above to browse its folders.';
                            return;
                        }
                        hint.textContent = 'Loading folders…';
                        fetch(dirsUrl + '?repo=' + encodeURIComponent(repo), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (dirs) {
                            dirs.forEach(function (d) {
                                var opt = document.createElement('option');
                                opt.value = d;
                                datalist.appendChild(opt);
                            });
                            hint.textContent = dirs.length
                                ? dirs.length + ' folders found — type to filter.'
                                : 'No subfolders found. Leave blank to use the whole repo.';
                        })
                        .catch(function () {
                            hint.textContent = 'Could not load folders — type the path manually.';
                        });
                    });
                })();
            </script>
        @endif
    </div>
</div>
@endsection
