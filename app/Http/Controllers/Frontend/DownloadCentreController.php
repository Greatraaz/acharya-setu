<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DownloadCentre;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadCentreController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $downloads = DownloadCentre::query()
            ->where('status', DownloadCentre::STATUS_ACTIVE)
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.download-centre.index', compact('downloads', 'search'));
    }

    public function download(string $slug): BinaryFileResponse
    {
        $item = DownloadCentre::query()
            ->where('slug', $slug)
            ->where('status', DownloadCentre::STATUS_ACTIVE)
            ->firstOrFail();

        $relative = PublicFileStorage::pathFromUrl($item->document) ?? ltrim((string) $item->document, '/');
        $absolute = storage_path('app/public/'.ltrim($relative, '/'));

        abort_unless(is_file($absolute) && is_readable($absolute), 404, 'Document file not found.');

        return response()->download($absolute, $item->downloadFilename());
    }
}
