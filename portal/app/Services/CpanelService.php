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
            // Treat "already exists" as success — a previous attempt may have partially registered it.
            if (str_contains($reason, 'already exists')) {
                return;
            }
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

    /**
     * Create a MySQL database via cPanel UAPI.
     * cPanel auto-prefixes with the account username.
     * Returns the full database name (e.g. traitor8921_panya).
     */
    public function createMysqlDatabase(string $suffix): string
    {
        $fullName = $this->user . '_' . $suffix;
        $result   = $this->uapi('Mysql', 'create_database', ['name' => $fullName]);

        $error = $result['errors'][0] ?? '';
        if (($result['status'] ?? 0) !== 1 && !str_contains($error, 'already exists')) {
            throw new \RuntimeException('cPanel failed to create database: ' . $error);
        }

        return $fullName;
    }

    /**
     * Create a MySQL user via cPanel UAPI.
     * Returns the full username (e.g. traitor8921_panya).
     */
    public function createMysqlUser(string $suffix, string $password): string
    {
        $fullName = $this->user . '_' . $suffix;
        $result   = $this->uapi('Mysql', 'create_user', [
            'name'     => $fullName,
            'password' => $password,
        ]);

        $error = $result['errors'][0] ?? '';
        if (($result['status'] ?? 0) !== 1 && !str_contains($error, 'already exists')) {
            throw new \RuntimeException('cPanel failed to create MySQL user: ' . $error);
        }

        return $fullName;
    }

    public function dropMysqlDatabase(string $fullName): void
    {
        $this->uapi('Mysql', 'delete_database', ['name' => $fullName]);
    }

    public function dropMysqlUser(string $fullName): void
    {
        $this->uapi('Mysql', 'delete_user', ['name' => $fullName]);
    }

    /**
     * Grant all privileges on a database to a user (both full prefixed names).
     */
    public function grantMysqlPrivileges(string $database, string $user): void
    {
        $result = $this->uapi('Mysql', 'set_privileges_on_database', [
            'user'       => $user,
            'database'   => $database,
            'privileges' => 'ALL PRIVILEGES',
        ]);

        if (($result['status'] ?? 0) !== 1) {
            throw new \RuntimeException('cPanel failed to grant MySQL privileges: ' . ($result['errors'][0] ?? 'unknown'));
        }
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
