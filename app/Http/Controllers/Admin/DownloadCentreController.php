<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadCentre;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DownloadCentreController extends Controller
{
    public function index(Request $request)
    {
        $query = DownloadCentre::query()->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $downloads = $query->paginate(20)->withQueryString();

        return view('admin.download-centres.index', compact('downloads'));
    }

    public function create()
    {
        $download = new DownloadCentre([
            'status' => DownloadCentre::STATUS_ACTIVE,
        ]);

        return view('admin.download-centres.create', compact('download'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'download-centres/images');
        $data['document'] = PublicFileStorage::store($request->file('document'), 'download-centres/documents');

        if (empty($data['slug'])) {
            $data['slug'] = DownloadCentre::uniqueSlug($data['title']);
        }

        DownloadCentre::create($data);

        return redirect()
            ->route('admin.download-centres.index')
            ->with('success', 'Download item created successfully.');
    }

    public function edit(DownloadCentre $downloadCentre)
    {
        return view('admin.download-centres.edit', ['download' => $downloadCentre]);
    }

    public function update(Request $request, DownloadCentre $downloadCentre)
    {
        $data = $this->validated($request, false, $downloadCentre);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($downloadCentre->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'download-centres/images');
        }

        if ($request->hasFile('document')) {
            PublicFileStorage::deleteByUrl($downloadCentre->document);
            $data['document'] = PublicFileStorage::store($request->file('document'), 'download-centres/documents');
        }

        if (empty($data['slug'])) {
            $data['slug'] = DownloadCentre::uniqueSlug($data['title'], $downloadCentre->id);
        }

        $downloadCentre->update($data);

        return redirect()
            ->route('admin.download-centres.index')
            ->with('success', 'Download item updated successfully.');
    }

    public function destroy(DownloadCentre $downloadCentre)
    {
        PublicFileStorage::deleteByUrl($downloadCentre->image);
        PublicFileStorage::deleteByUrl($downloadCentre->document);
        $downloadCentre->delete();

        return redirect()
            ->route('admin.download-centres.index')
            ->with('success', 'Download item deleted.');
    }

    private function validated(Request $request, bool $requireFiles, ?DownloadCentre $existing = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('download_centres', 'slug')->ignore($existing?->id),
            ],
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'image' => ($requireFiles ? 'required' : 'nullable').'|image|max:4096',
            'document' => ($requireFiles ? 'required' : 'nullable').'|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:20480',
        ];

        $data = $request->validate($rules);
        unset($data['image'], $data['document']);

        if (! empty($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        return $data;
    }
}
