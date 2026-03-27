<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GitHubService
{
    private string $appId;
    private string $privateKey;
    private string $webhookSecret;
    private string $appSlug;

    public function __construct()
    {
        $this->appId          = config('services.github.app_id') ?? throw new \RuntimeException('GITHUB_APP_ID not configured.');
        $this->appSlug        = config('services.github.app_slug') ?? throw new \RuntimeException('GITHUB_APP_SLUG not configured.');
        $this->webhookSecret  = config('services.github.webhook_secret') ?? throw new \RuntimeException('GITHUB_WEBHOOK_SECRET not configured.');

        $b64 = config('services.github.private_key_b64') ?? throw new \RuntimeException('GITHUB_APP_PRIVATE_KEY_BASE64 not configured.');
        $this->privateKey = base64_decode($b64);
    }

    public function installUrl(): string
    {
        return "https://github.com/apps/{$this->appSlug}/installations/new";
    }

    public function listRepos(int $installationId): array
    {
        $token = $this->getInstallationToken($installationId);

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->get('https://api.github.com/installation/repositories', ['per_page' => 100]);

        if (!$response->successful()) {
            throw new \RuntimeException('GitHub API error listing repos: ' . $response->body());
        }

        return collect($response->json('repositories'))
            ->map(fn($r) => ['full_name' => $r['full_name'], 'default_branch' => $r['default_branch'] ?? 'main'])
            ->all();
    }

    public function listBranches(int $installationId, string $repo): array
    {
        $token = $this->getInstallationToken($installationId);

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->get("https://api.github.com/repos/{$repo}/branches", ['per_page' => 100]);

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub branches API error for {$repo}: " . $response->body());
        }

        return collect($response->json())
            ->pluck('name')
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Returns all directory paths in the repo (type=tree), sorted.
     * Uses the recursive tree API so it's one request regardless of depth.
     */
    public function listDirs(int $installationId, string $repo, string $ref = 'HEAD'): array
    {
        $token = $this->getInstallationToken($installationId);

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->get("https://api.github.com/repos/{$repo}/git/trees/{$ref}", ['recursive' => 1]);

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub tree API error for {$repo}: " . $response->body());
        }

        return collect($response->json('tree') ?? [])
            ->where('type', 'tree')
            ->pluck('path')
            ->sort()
            ->values()
            ->all();
    }

    public function downloadZipball(int $installationId, string $repo, string $ref = 'HEAD'): string
    {
        $token = $this->getInstallationToken($installationId);

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->withOptions(['allow_redirects' => true])
            ->get("https://api.github.com/repos/{$repo}/zipball/{$ref}");

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub zipball download failed for {$repo}@{$ref}: " . $response->body());
        }

        $tmpPath = sys_get_temp_dir() . '/github_' . uniqid() . '.zip';
        file_put_contents($tmpPath, $response->body());

        return $tmpPath;
    }

    public function setCommitStatus(int $installationId, string $repo, string $sha, string $state, string $description): void
    {
        $token = $this->getInstallationToken($installationId);

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->post("https://api.github.com/repos/{$repo}/statuses/{$sha}", [
                'state'       => $state,
                'description' => $description,
                'context'     => 'traitor.dev',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub status update failed: " . $response->body());
        }
    }

    public function deleteInstallation(int $installationId): void
    {
        $jwt = $this->generateJwt();

        $response = Http::withToken($jwt, 'Bearer')
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->delete("https://api.github.com/app/installations/{$installationId}");

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub delete installation failed: " . $response->body());
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }

    private function getInstallationToken(int $installationId): string
    {
        $jwt = $this->generateJwt();

        $response = Http::withToken($jwt, 'Bearer')
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->post("https://api.github.com/app/installations/{$installationId}/access_tokens");

        if (!$response->successful()) {
            throw new \RuntimeException("GitHub installation token error: " . $response->body());
        }

        return $response->json('token');
    }

    private function generateJwt(): string
    {
        $header  = $this->base64urlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now     = time();
        $payload = $this->base64urlEncode(json_encode([
            'iat' => $now - 60,
            'exp' => $now + 600,
            'iss' => (int) $this->appId,
        ]));

        $data = $header . '.' . $payload;
        openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return $data . '.' . $this->base64urlEncode($signature);
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
