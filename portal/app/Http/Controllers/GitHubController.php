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
        $request->validate(['repo' => ['required', 'string', 'regex:/^[\w.\-]+\/[\w.\-]+$/']]);

        $site->update(['github_repo' => $request->input('repo')]);

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

        $data          = json_decode($payload, true);
        $ref           = $data['ref'] ?? '';
        $defaultBranch = $data['repository']['default_branch'] ?? 'main';

        if ($ref !== "refs/heads/{$defaultBranch}") {
            return response()->json(['message' => 'not default branch, ignored']);
        }

        $installationId = $data['installation']['id'] ?? null;
        $repoFullName   = $data['repository']['full_name'] ?? null;

        abort_unless($installationId && $repoFullName, 400, 'Missing installation or repository in payload.');

        $site = Site::withoutGlobalScopes()
            ->where('github_installation_id', $installationId)
            ->where('github_repo', $repoFullName)
            ->firstOrFail();

        $zipPath = $this->github->downloadZipball($installationId, $repoFullName, $data['after']);

        try {
            $this->siteService->uploadFromPath($site, $zipPath);
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
