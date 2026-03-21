<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteApiController extends Controller
{
    public function __construct(private SiteService $siteService) {}

    public function index(): JsonResponse
    {
        return response()->json(Site::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'zip' => 'nullable|file|mimes:zip|max:51200',
        ]);

        $slug = Str::slug($request->name);

        if (Site::where('slug', $slug)->exists()) {
            return response()->json(['error' => 'Site already exists.'], 409);
        }

        $site = $this->siteService->create($request->name, $slug);

        if ($request->hasFile('zip')) {
            $this->siteService->upload($site, $request->file('zip'));
        }

        return response()->json($site, 201);
    }

    public function show(Site $site): JsonResponse
    {
        return response()->json($site->load('releases'));
    }

    public function upload(Request $request, Site $site): JsonResponse
    {
        $request->validate([
            'zip' => 'required|file|mimes:zip|max:51200',
        ]);

        $this->siteService->upload($site, $request->file('zip'));

        return response()->json(['message' => 'Uploaded.']);
    }

    public function publish(Request $request, Site $site): JsonResponse
    {
        $release = $this->siteService->publish($site, $request->input('notes'));

        return response()->json(['message' => 'Published.', 'version' => $release->version]);
    }

    public function rollback(Request $request, Site $site): JsonResponse
    {
        $release = $this->siteService->rollback($site, $request->input('version'));

        return response()->json(['message' => 'Rolled back.', 'version' => $release->version]);
    }

    public function destroy(Site $site): JsonResponse
    {
        $this->siteService->delete($site);

        return response()->json(['message' => 'Deleted.']);
    }
}
