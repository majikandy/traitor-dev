<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'zip' => 'nullable|file|mimes:zip|max:51200', // 50MB max
        ]);

        $slug = Str::slug($request->name);

        if (Site::where('slug', $slug)->exists()) {
            return back()->withInput()->with('error', 'A site with that name already exists.');
        }

        $site = $this->siteService->create($request->name, $slug);

        if ($request->hasFile('zip')) {
            $this->siteService->upload($site, $request->file('zip'));
        }

        return redirect()->route('sites.show', $site)->with('success', 'Site created! Upload your files or publish to go live.');
    }

    public function show(Site $site)
    {
        $site->load('releases');

        return view('sites.show', compact('site'));
    }

    public function upload(Request $request, Site $site)
    {
        $request->validate([
            'zip' => 'required|file|mimes:zip|max:51200',
        ]);

        $this->siteService->upload($site, $request->file('zip'));

        return redirect()->route('sites.show', $site)->with('success', 'Files uploaded to drafts.');
    }

    public function publish(Request $request, Site $site)
    {
        $this->siteService->publish($site, $request->input('notes'));

        return redirect()->route('sites.show', $site)->with('success', 'Published! Site is now live.');
    }

    public function rollback(Site $site)
    {
        if ($site->current_release <= 1) {
            return back()->with('error', 'Nothing to roll back to.');
        }

        $release = $this->siteService->rollback($site);

        return redirect()->route('sites.show', $site)->with('success', "Rolled back to version {$release->version}.");
    }

    public function destroy(Site $site)
    {
        $name = $site->name;
        $this->siteService->delete($site);

        return redirect()->route('sites.index')->with('success', "Deleted {$name}.");
    }
}
