<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\Site;
use App\Services\GitHubService;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GitHubController extends Controller
{
    public function __construct(
        private GitHubService $github,
        private SiteService $siteService,
    ) {}

    /**
     * Kick off the GitHub App installation flow.
     * We remember which site the user came from so we can redirect back after.
     */
    public function install(Site $site): RedirectResponse
    {
        session([
            'github_connect_site_id' => $site->id,
            'github_connect_org_id'  => Auth::user()->organisation_id,
        ]);

        return redirect($this->github->installUrl());
    }

    /**
     * GitHub redirects back here after installation.
     * Save the installation_id on the organisation, then send the user
     * to the repo-picker for their site.
     */
    public function callback(Request $request): RedirectResponse
    {
        $siteId         = session('github_connect_site_id');
        $orgId          = session('github_connect_org_id');
        $installationId = $request->integer('installation_id');
        $action         = $request->input('setup_action');

        abort_unless($siteId && $orgId && $installationId && in_array($action, ['install', 'update']), 400, 'Invalid GitHub callback.');

        Organisation::findOrFail($orgId)->update(['github_installation_id' => $installationId]);

        session()->forget(['github_connect_site_id', 'github_connect_org_id']);

        $site = Site::findOrFail($siteId);

        return redirect()->route('github.select-repo-form', $site)
            ->with('success', 'GitHub App installed. Now pick a repository for this site.');
    }

    /**
     * Show the repo picker for a site whose org already has a GitHub installation.
     */
    public function selectRepoForm(Site $site): View
    {
        $org  = $site->organisation;

        abort_unless($org->hasGitHub(), 400, 'No GitHub installation for this organisation.');

        $repoData      = $this->github->listRepos($org->github_installation_id);
        $repos         = array_column($repoData, 'full_name');
        $defaultBranches = array_column($repoData, 'default_branch', 'full_name');

        return view('github.select-repo', compact('site', 'repos', 'defaultBranches'));
    }

    public function repoBranches(Request $request, Site $site): JsonResponse
    {
        $repo = $request->string('repo')->toString();
        abort_unless(preg_match('/^[\w.\-]+\/[\w.\-]+$/', $repo), 422, 'Invalid repo.');

        $org = $site->organisation;
        abort_unless($org->hasGitHub(), 400, 'No GitHub installation for this organisation.');

        return response()->json($this->github->listBranches($org->github_installation_id, $repo));
    }

    /**
     * Return a JSON list of directories in a repo — used by the subfolder picker.
     */
    public function repoDirs(Request $request, Site $site): JsonResponse
    {
        $repo = $request->string('repo')->toString();
        abort_unless(preg_match('/^[\w.\-]+\/[\w.\-]+$/', $repo), 422, 'Invalid repo.');

        $org  = $site->organisation;
        abort_unless($org->hasGitHub(), 400, 'No GitHub installation for this organisation.');

        $dirs = $this->github->listDirs($org->github_installation_id, $repo);

        return response()->json($dirs);
    }

    public function selectRepo(Request $request, Site $site): RedirectResponse
    {
        $request->validate([
            'repo'      => ['required', 'string', 'regex:/^[\w.\-]+\/[\w.\-]+$/'],
            'repo_path' => ['nullable', 'string', 'max:255'],
            'branch'    => ['nullable', 'string', 'max:255'],
        ]);

        $repo     = $request->input('repo');
        $repoPath = $request->filled('repo_path') ? trim($request->input('repo_path'), '/') : null;
        $branch   = $request->filled('branch') ? $request->input('branch') : null;

        $site->update([
            'github_repo'      => $repo,
            'github_repo_path' => $repoPath,
            'github_branch'    => $branch,
        ]);

        $installationId = $site->organisation->github_installation_id;
        $ref            = $branch ?? 'HEAD';
        $zipPath        = $this->github->downloadZipball($installationId, $repo, $ref);

        try {
            $this->siteService->uploadFromPath($site, $zipPath, $repoPath);
            $release = $this->siteService->createRelease($site, "Initial import from {$repo}");
        } finally {
            @unlink($zipPath);
        }

        return redirect()->route('sites.show', $site)
            ->with('success', "GitHub connected and release v{$release->version} created from {$repo}.");
    }

    public function toggleAutoDeploy(Site $site): RedirectResponse
    {
        $site->update(['github_auto_deploy' => !$site->github_auto_deploy]);

        return redirect()->route('sites.show', $site);
    }

    /**
     * Disconnect the repo from this site only.
     * The org-level GitHub installation is NOT removed here — other sites may use it.
     */
    public function disconnect(Site $site): RedirectResponse
    {
        $site->update([
            'github_repo'        => null,
            'github_repo_path'   => null,
            'github_branch'      => null,
            'github_auto_deploy' => false,
        ]);

        return redirect()->route('sites.show', $site)->with('success', 'GitHub repository disconnected.');
    }

    /**
     * Receive push events from GitHub.
     * Fan out to all sites in the org that watch the pushed repo.
     * Download the zipball once and reuse it across multiple sites.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256', '');

        abort_unless($this->github->verifyWebhookSignature($payload, $signature), 403, 'Invalid webhook signature.');

        $event = $request->header('X-GitHub-Event');

        if ($event !== 'push') {
            return response()->json(['message' => 'ignored']);
        }

        $data           = json_decode($payload, true);
        $ref            = $data['ref'] ?? '';
        $defaultBranch  = $data['repository']['default_branch'] ?? 'main';
        $installationId = $data['installation']['id'] ?? null;
        $repoFullName   = $data['repository']['full_name'] ?? null;
        $sha            = $data['after'] ?? null;

        abort_unless($installationId && $repoFullName && $sha, 400, 'Missing required fields in webhook payload.');

        $org = Organisation::where('github_installation_id', $installationId)->firstOrFail();

        $sites = Site::withoutGlobalScopes()
            ->where('organisation_id', $org->id)
            ->where('github_repo', $repoFullName)
            ->get();

        if ($sites->isEmpty()) {
            return response()->json(['message' => 'no sites watching this repo']);
        }

        // Filter to sites where the push is on the watched branch
        $sites = $sites->filter(function (Site $site) use ($ref, $defaultBranch) {
            $watchBranch = $site->github_branch ?? $defaultBranch;
            return $ref === "refs/heads/{$watchBranch}";
        });

        if ($sites->isEmpty()) {
            return response()->json(['message' => 'not watched branch, ignored']);
        }

        // Further filter by path if set — a site watching a subfolder only deploys when that path changes
        $changedFiles = collect($data['commits'] ?? [])
            ->flatMap(fn($c) => array_merge($c['added'] ?? [], $c['modified'] ?? [], $c['removed'] ?? []))
            ->all();

        $sites = $sites->filter(function (Site $site) use ($changedFiles) {
            if ($site->github_repo_path === null) {
                return true;
            }
            $prefix = rtrim($site->github_repo_path, '/') . '/';
            return !empty(array_filter($changedFiles, fn($f) => str_starts_with($f, $prefix)));
        });

        if ($sites->isEmpty()) {
            return response()->json(['message' => 'no changes in any watched repo path, ignored']);
        }

        // Set pending status on all affected sites
        foreach ($sites as $site) {
            $this->github->setCommitStatus($installationId, $repoFullName, $sha, 'pending', 'Creating release...');
        }

        // Download zipball once for all sites
        $zipPath = $this->github->downloadZipball($installationId, $repoFullName, $sha);

        $results = [];

        try {
            foreach ($sites as $site) {
                try {
                    $this->siteService->uploadFromPath($site, $zipPath, $site->github_repo_path);
                    $release = $this->siteService->createRelease(
                        $site,
                        'Auto-deployed from GitHub (' . substr($sha, 0, 7) . ')'
                    );

                    if ($site->github_auto_deploy) {
                        $this->siteService->promote($site, $release->version);
                    }

                    $this->github->setCommitStatus($installationId, $repoFullName, $sha, 'success', 'Release v' . $release->version . ' created');
                    $results[$site->slug] = ['version' => $release->version];
                } catch (\Throwable $e) {
                    $this->github->setCommitStatus($installationId, $repoFullName, $sha, 'failure', 'Release failed: ' . $e->getMessage());
                    $results[$site->slug] = ['error' => $e->getMessage()];
                }
            }
        } finally {
            @unlink($zipPath);
        }

        return response()->json(['message' => 'processed', 'sites' => $results]);
    }
}
