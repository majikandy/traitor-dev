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
        $this->host       = config('services.cpanel.host');
        $this->user       = config('services.cpanel.user');
        $this->token      = config('services.cpanel.token');
        $this->rootDomain = config('services.cpanel.root_domain');
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

    public function triggerAutoSsl(): void
    {
        $this->uapi('SSL', 'start_autossl_check');
    }

    private function subdomainHandle(string $domain): string
    {
        return str_replace('.', '-', $domain);
    }

    private function v2(string $module, string $func, array $params): array
    {
        $response = Http::withHeaders(['Authorization' => "cpanel {$this->user}:{$this->token}"])
            ->withoutVerifying()
            ->get("https://{$this->host}:2083/json-api/cpanel", array_merge([
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
            ->get("https://{$this->host}:2083/execute/{$module}/{$func}", $params);

        return $response->json() ?? [];
    }
}
