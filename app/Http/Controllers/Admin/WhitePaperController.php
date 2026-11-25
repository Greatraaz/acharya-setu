<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhitePaper;
use App\Models\WhitePaperDownload;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;

class WhitePaperController extends Controller
{
    public function index(Request $request)
    {
        $query = WhitePaper::query()
            ->withCount('downloads')
            ->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $whitePapers = $query->paginate(20)->withQueryString();

        return view('admin.white-papers.index', compact('whitePapers'));
    }

    public function downloads(Request $request)
    {
        $query = WhitePaperDownload::query()
            ->with('paper:id,title,slug')
            ->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhereHas('paper', fn ($g) => $g->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('paper_id')) {
            $query->where('white_paper_id', $request->integer('paper_id'));
        }

        $downloads = $query->paginate(30)->withQueryString();
        $papers = WhitePaper::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.white-papers.downloads', compact('downloads', 'papers'));
    }

    public function create()
    {
        $whitePaper = new WhitePaper([
            'status' => WhitePaper::STATUS_ACTIVE,
        ]);

        return view('admin.white-papers.create', compact('whitePaper'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'white-papers');

        WhitePaper::create($data);

        return redirect()
            ->route('admin.white-papers.index')
            ->with('success', 'White paper created successfully.');
    }

    public function edit(WhitePaper $whitePaper)
    {
        return view('admin.white-papers.edit', compact('whitePaper'));
    }

    public function update(Request $request, WhitePaper $whitePaper)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($whitePaper->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'white-papers');
        }

        $whitePaper->update($data);

        return redirect()
            ->route('admin.white-papers.index')
            ->with('success', 'White paper updated successfully.');
    }

    public function destroy(WhitePaper $whitePaper)
    {
        PublicFileStorage::deleteByUrl($whitePaper->image);
        $whitePaper->delete();

        return redirect()
            ->route('admin.white-papers.index')
            ->with('success', 'White paper deleted.');
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ];

        $data = $request->validate($rules);
        unset($data['image']);

        return $data;
    }
}
