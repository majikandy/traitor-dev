<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CpanelService
{
    private string $host;
    private string $user;
    private string $token;

    private string $rootDomain;

    public function __construct()
    {
        $this->host       = config('services.cpanel.host')        ?? throw new \RuntimeException('CPANEL_HOST is not set in .env');
        $this->user       = config('services.cpanel.user')        ?? throw new \RuntimeException('CPANEL_USER is not set in .env');
        $this->token      = config('services.cpanel.token')       ?? throw new \RuntimeException('CPANEL_TOKEN is not set in .env');
        $this->rootDomain = config('services.cpanel.root_domain') ?? throw new \RuntimeException('CPANEL_ROOT_DOMAIN is not set in .env');
    }

    public function createAddonDomain(string $domain, string $docroot): void
    {
        $result = $this->v2('AddonDomain', 'addaddondomain', [
            'newdomain' => $domain,
            'subdomain' => $this->subdomainHandle($domain),
            'dir'       => $docroot,
        ]);

        if (!($result['data'][0]['result'] ?? false)) {
            $reason = $result['data'][0]['reason'] ?? 'Unknown error';
            throw new \RuntimeException("cPanel failed to create addon domain: {$reason}");
        }
    }

    public function removeAddonDomain(string $domain): void
    {
        $this->v2('AddonDomain', 'deladdondomain', [
            'domain'    => $domain,
            'subdomain' => $this->subdomainHandle($domain) . '.' . $this->rootDomain,
        ]);
    }

    public function createPreviewSubdomain(string $slug, string $docroot): void
    {
        $previewDomain = config('services.cpanel.preview_domain')
            ?? throw new \RuntimeException('CPANEL_PREVIEW_DOMAIN is not set in .env');

        // UAPI requires rootdomain to be the main cPanel domain (not an addon domain).
        // We pass "{slug}.preview" as the domain to create {slug}.preview.{rootDomain}.
        $previewSubdomain = rtrim(str_replace($this->rootDomain, '', $previewDomain), '.');

        $result = $this->uapi('SubDomain', 'addsubdomain', [
            'domain'     => $slug . '.' . $previewSubdomain,
            'rootdomain' => $this->rootDomain,
            'dir'        => $docroot,
        ]);

        if (($result['status'] ?? 0) !== 1) {
            $reason = $result['errors'][0] ?? 'Unknown error';
            if (str_contains((string) $reason, 'already exists')) {
                // Delete and recreate so the docroot is always correct
                $this->removePreviewSubdomain($slug);
                $this->uapi('SubDomain', 'addsubdomain', [
                    'domain'     => $slug . '.' . $previewSubdomain,
                    'rootdomain' => $this->rootDomain,
                    'dir'        => $docroot,
                ]);
            } else {
                throw new \RuntimeException("cPanel failed to create preview subdomain: {$reason}");
            }
        }
    }

    public function removePreviewSubdomain(string $slug): void
    {
        $previewDomain = config('services.cpanel.preview_domain')
            ?? throw new \RuntimeException('CPANEL_PREVIEW_DOMAIN is not set in .env');

        $this->v2('SubDomain', 'delsubdomain', [
            'domain' => $slug . '.' . $previewDomain . '.' . $this->rootDomain,
        ]);
    }



    public function triggerAutoSsl(): void
    {
        $this->uapi('SSL', 'start_autossl_check');
    }

    public function listAddonDomains(): array
    {
        $result = $this->uapi('AddonDomain', 'listaddondomains');
        return $result['data'] ?? [];
    }

    public function listSubdomains(): array
    {
        $result = $this->v2('SubDomain', 'listsubdomains', []);
        return $result['data'] ?? [];
    }

    public function getDiskUsage(): array
    {
        $result = $this->uapi('Quota', 'get_quota_info');
        return $result['data'] ?? [];
    }

    public function listSslDomains(): array
    {
        $result = $this->uapi('SSL', 'list_certs');
        return $result['data'] ?? [];
    }

    private function subdomainHandle(string $domain): string
    {
        return str_replace('.', '-', $domain);
    }

    private function v2(string $module, string $func, array $params): array
    {
        $response = Http::withHeaders(['Authorization' => "cpanel {$this->user}:{$this->token}"])
            ->withoutVerifying()
            ->timeout(15)
            ->asForm()
            ->post("https://{$this->host}:2083/json-api/cpanel", array_merge([
                'cpanel_jsonapi_apiversion' => '2',
                'cpanel_jsonapi_module'     => $module,
                'cpanel_jsonapi_func'       => $func,
            ], $params));

        return $response->json('cpanelresult') ?? [];
    }

    private function uapi(string $module, string $func, array $params = []): array
    {
        $response = Http::withHeaders(['Authorization' => "cpanel {$this->user}:{$this->token}"])
            ->withoutVerifying()
            ->timeout(15)
            ->asForm()
            ->post("https://{$this->host}:2083/execute/{$module}/{$func}", $params);

        return $response->json() ?? [];
    }
}
