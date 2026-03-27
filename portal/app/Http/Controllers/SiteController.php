<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Site;
use App\Services\CpanelService;
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

        if (Site::where('slug', $slug)->exists()) {
            return back()->withInput()->with('error', 'You already have a site with that name.');
        }

        $site = $this->siteService->create($request->name, $slug, Auth::user()->organisation_id);

        return redirect()->route('sites.show', $site)->with('success', 'Site created! Upload a zip to create your first release.');
    }

    public function show(Site $site)
    {
        $site->load('releases');

        return view('sites.show', compact('site'));
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

        if (Site::where('domain', $domain)->where('id', '!=', $site->id)->exists()) {
            return back()->withErrors(['domain' => 'That domain is already attached to another site.']);
        }

        $this->siteService->attachDomain($site, $domain, $cpanel);

        return back()->with('success', "Domain {$domain} attached. Now point its A record to " . config('app.server_ip') . '.');
    }

    public function detachDomain(Site $site, CpanelService $cpanel)
    {
        $this->siteService->detachDomain($site, $cpanel);

        return back()->with('success', 'Domain removed.');
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

    public function update(Request $request, Site $site)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $site->update(['name' => $request->name]);

        return back()->with('success', 'Site renamed.');
    }

    public function toggleMaintenance(Site $site)
    {
        if ($site->maintenance_mode) {
            $this->siteService->disableMaintenance($site);
            return back()->with('success', 'Site is back online.');
        }

        $this->siteService->enableMaintenance($site);
        return back()->with('success', 'Maintenance mode enabled — visitors now see the coming soon page.');
    }

    public function destroy(Site $site)
    {
        $name = $site->name;
        $this->siteService->delete($site);

        return redirect()->route('sites.index')->with('success', "Deleted {$name}.");
    }
}
