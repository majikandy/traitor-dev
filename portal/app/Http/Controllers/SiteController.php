<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Site;
use App\Services\CpanelService;
use App\Services\GitHubService;
use App\Services\SiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class SiteController extends Controller
{
    public function __construct(private SiteService $siteService) {}

    public function index()
    {
        $sites = Site::latest()->get();

        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        return view('sites.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        if ($slug === 'shared') {
            return back()->withInput()->with('error', '"shared" is a reserved name.');
        }

        if (Site::where('slug', $slug)->exists()) {
            return back()->withInput()->with('error', 'You already have a site with that name.');
        }

        $site = $this->siteService->create($request->name, $slug, Auth::user()->organisation_id);

        return redirect()->route('sites.show', $site)->with('success', 'Site created! Upload a zip to create your first release.');
    }

    public function show(Site $site)
    {
        $site->load('releases', 'organisation');

        $needsLaravelSetup = $site->type === 'laravel' && !$this->siteService->hasSharedEnv($site);

        return view('sites.show', compact('site', 'needsLaravelSetup'));
    }

    public function shareVersionPreview(Site $site, int $version)
    {
        $release = $site->releases()->where('version', $version)->firstOrFail();
        $release->update(['preview_shared' => true]);
        $url = $this->versionPreviewUrl($site, $release->preview_token, $version);
        return response()->json(['url' => $url]);
    }

    public function regenerateVersionPreviewToken(Site $site, int $version)
    {
        $token = $this->siteService->rotatePreviewToken($site, $version);
        $url = $this->versionPreviewUrl($site, $token, $version);
        return response()->json(['url' => $url]);
    }

    public function revokeVersionPreview(Site $site, int $version)
    {
        $this->siteService->rotatePreviewToken($site, $version);
        $site->releases()->where('version', $version)->update(['preview_shared' => false]);
        return back();
    }

    private function versionPreviewUrl(Site $site, string $token, int $version): string
    {
        return 'https://' . $site->slug . '-v' . $version . '.' . config('services.cpanel.preview_domain') . '?token=' . $token;
    }

    public function createRelease(Request $request, Site $site)
    {
        $request->validate([
            'zip' => 'required|file|mimes:zip|max:51200',
        ]);

        $this->siteService->upload($site, $request->file('zip'));
        $release = $this->siteService->createRelease($site, $request->input('notes'));

        return redirect()->route('sites.show', $site)->with('success', "Release v{$release->version} created.");
    }

    public function downloadDraft(Site $site): BinaryFileResponse
    {
        return $this->zipAndDownload($site->draftsPath(), $site->slug . '-draft.zip');
    }

    public function downloadRelease(Site $site, Release $release): BinaryFileResponse
    {
        return $this->zipAndDownload($site->releasePath($release->version), $site->slug . '-v' . $release->version . '.zip');
    }

    private function zipAndDownload(string $sourcePath, string $filename): BinaryFileResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'site-download-');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create zip file.');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $relativePath = substr($file->getPathname(), strlen($sourcePath) + 1);
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    public function attachDomain(Request $request, Site $site, CpanelService $cpanel)
    {
        $request->validate(['domain' => 'required|string|max:255|regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/']);

        $domain = strtolower($request->domain);

        $platformDomain = config('app.platform_domain');
        if (!auth()->user()->is_admin && ($domain === $platformDomain || str_ends_with($domain, '.' . $platformDomain))) {
            return back()->withErrors(['domain' => "Cannot attach {$platformDomain} domains."]);
        }

        if (Site::where('domain', $domain)->where('id', '!=', $site->id)->exists()) {
            return back()->withErrors(['domain' => 'That domain is already attached to another site.']);
        }

        $this->siteService->attachDomain($site, $domain, $cpanel);

        return back()->with('success', "Domain {$domain} attached. Now point its A record to " . config('app.server_ip') . '.');
    }

    public function detachDomain(Site $site, CpanelService $cpanel)
    {
        $platformDomain = config('app.platform_domain');
        $isManual = $site->domain === $platformDomain || str_ends_with((string) $site->domain, '.' . $platformDomain);

        if ($isManual) {
            $site->update(['domain' => null, 'domain_status' => null]);
        } else {
            $this->siteService->detachDomain($site, $cpanel);
        }

        return back()->with('success', 'Domain removed.');
    }

    public function forceActiveDomain(Request $request, Site $site)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $request->validate(['domain' => 'required|string|max:255|regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/']);

        $domain = strtolower($request->domain);

        if (Site::where('domain', $domain)->where('id', '!=', $site->id)->exists()) {
            return back()->withErrors(['force_domain' => 'That domain is already attached to another site.']);
        }

        $site->update(['domain' => $domain, 'domain_status' => 'active']);

        return back()->with('success', "Domain {$domain} marked as active (manually managed — no cPanel).");
    }

    public function checkDns(Site $site, CpanelService $cpanel)
    {
        if ($this->siteService->checkDns($site)) {
            $site->update(['domain_status' => 'active']);
            $cpanel->triggerAutoSsl();
            return back()->with('success', 'DNS verified! SSL is being provisioned automatically.');
        }

        return back()->with('error', 'DNS not yet propagated — ' . $site->domain . ' does not point to this server yet. Try again in a few minutes.');
    }

    public function promoteRelease(Site $site, int $version)
    {
        $release = $site->releases()->where('version', $version)->firstOrFail();

        $this->siteService->promote($site, $release->version);

        if (request()->expectsJson()) {
            return response()->json(['version' => $version]);
        }

        return back()->with('success', "v{$release->version} is now live.");
    }

    public function revertToComingSoon(Site $site)
    {
        $this->siteService->revertToComingSoon($site);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Reverted to coming soon page.');
    }

    public function update(Request $request, Site $site)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $site->update(['name' => $request->name]);

        return back()->with('success', 'Site renamed.');
    }

    public function toggleMaintenance(Site $site, Request $request)
    {
        if ($site->maintenance_mode) {
            $this->siteService->disableMaintenance($site);
            return back();
        }

        $request->validate([
            'maintenance_page' => 'in:brb,countdown',
            'launch_date'      => 'nullable|date_format:Y-m-d\TH:i|required_if:maintenance_page,countdown',
        ]);

        $page       = $request->input('maintenance_page', 'brb');
        $launchDate = $request->filled('launch_date') ? new \DateTime($request->input('launch_date')) : null;

        $this->siteService->enableMaintenance($site, $page, $launchDate);
        return back();
    }

    public function laravelSetupForm(Site $site)
    {
        abort_unless($site->type === 'laravel', 404);

        $envExists = $this->siteService->hasSharedEnv($site);

        return view('sites.laravel-setup', compact('site', 'envExists'));
    }

    public function laravelSetup(Request $request, Site $site, CpanelService $cpanel)
    {
        abort_unless($site->type === 'laravel', 404);

        $envAlreadyExists = $this->siteService->hasSharedEnv($site);

        if (!$envAlreadyExists) {
            $request->validate([
                'db_suffix' => ['required', 'regex:/^[a-z0-9_]+$/'],
            ]);
            $creds = $this->siteService->setupDatabase($site, $cpanel, $request->input('db_suffix'));
        }

        $release = $this->siteService->createRelease($site, 'Initial release');

        $flash = ['version' => $release->version];

        if (!$envAlreadyExists) {
            $flash += [
                'db_name' => $creds['dbName'],
                'db_user' => $creds['dbUser'],
                'db_pass' => $creds['dbPass'],
            ];
        }

        return redirect()->route('sites.show', $site)->with('laravel_creds', $flash);
    }

    public function destroy(Site $site, GitHubService $github)
    {
        $name = $site->name;
        $this->siteService->delete($site, $github);

        return redirect()->route('sites.index')->with('success', "Deleted {$name}.");
    }
}
