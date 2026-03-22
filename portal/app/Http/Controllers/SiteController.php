<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\Request;
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
            return back()->withInput()->with('error', 'A site with that name already exists.');
        }

        $site = $this->siteService->create($request->name, $slug);

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

    public function destroy(Site $site)
    {
        $name = $site->name;
        $this->siteService->delete($site);

        return redirect()->route('sites.index')->with('success', "Deleted {$name}.");
    }
}
