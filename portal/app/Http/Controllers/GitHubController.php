<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\GitHubService;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GitHubController extends Controller
{
    public function __construct(
        private GitHubService $github,
        private SiteService $siteService,
    ) {}

    public function install(Site $site): RedirectResponse
    {
        session(['github_connect_site_id' => $site->id]);
        return redirect($this->github->installUrl());
    }

    public function callback(Request $request): RedirectResponse|View
    {
        $siteId        = session('github_connect_site_id');
        $installationId = $request->integer('installation_id');
        $action        = $request->input('setup_action');

        abort_unless($siteId && $installationId && $action === 'install', 400, 'Invalid GitHub callback.');

        $site = Site::findOrFail($siteId);
        $site->update(['github_installation_id' => $installationId, 'github_repo' => null]);

        session()->forget('github_connect_site_id');

        $repos = $this->github->listRepos($installationId);

        return view('github.select-repo', compact('site', 'repos'));
    }

    public function selectRepo(Request $request, Site $site): RedirectResponse
    {
        $request->validate([
            'repo'      => ['required', 'string', 'regex:/^[\w.\-]+\/[\w.\-]+$/'],
            'repo_path' => ['nullable', 'string', 'max:255'],
            'branch'    => ['nullable', 'string', 'max:255'],
        ]);

        $site->update([
            'github_repo'      => $request->input('repo'),
            'github_repo_path' => $request->filled('repo_path') ? trim($request->input('repo_path'), '/') : null,
            'github_branch'    => $request->filled('branch') ? $request->input('branch') : null,
        ]);

        return redirect()->route('sites.show', $site)->with('success', 'GitHub repository connected.');
    }

    public function toggleAutoDeploy(Site $site): RedirectResponse
    {
        $site->update(['github_auto_deploy' => !$site->github_auto_deploy]);

        return redirect()->route('sites.show', $site);
    }

    public function disconnect(Site $site): RedirectResponse
    {
        $site->update([
            'github_installation_id' => null,
            'github_repo'            => null,
            'github_repo_path'       => null,
            'github_branch'          => null,
            'github_auto_deploy'     => false,
        ]);

        return redirect()->route('sites.show', $site)->with('success', 'GitHub disconnected.');
    }

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

        abort_unless($installationId && $repoFullName, 400, 'Missing installation or repository in payload.');

        $site = Site::withoutGlobalScopes()
            ->where('github_installation_id', $installationId)
            ->where('github_repo', $repoFullName)
            ->firstOrFail();

        $watchBranch = $site->github_branch ?? $defaultBranch;

        if ($ref !== "refs/heads/{$watchBranch}") {
            return response()->json(['message' => 'not watched branch, ignored']);
        }

        if ($site->github_repo_path !== null) {
            $changedFiles = collect($data['commits'] ?? [])
                ->flatMap(fn($c) => array_merge($c['added'] ?? [], $c['modified'] ?? [], $c['removed'] ?? []))
                ->all();

            $prefix = rtrim($site->github_repo_path, '/') . '/';
            $relevant = array_filter($changedFiles, fn($f) => str_starts_with($f, $prefix));

            if (empty($relevant)) {
                return response()->json(['message' => 'no changes in repo path, ignored']);
            }
        }

        $zipPath = $this->github->downloadZipball($installationId, $repoFullName, $data['after']);

        try {
            $this->siteService->uploadFromPath($site, $zipPath, $site->github_repo_path);
            $release = $this->siteService->createRelease(
                $site,
                'Auto-deployed from GitHub (' . substr($data['after'], 0, 7) . ')'
            );

            if ($site->github_auto_deploy) {
                $this->siteService->promote($site, $release->version);
            }
        } finally {
            @unlink($zipPath);
        }

        return response()->json(['message' => 'deployed', 'version' => $release->version]);
    }
}
