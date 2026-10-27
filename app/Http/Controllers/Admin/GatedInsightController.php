<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatedInsightDownload;
use App\Models\GatedInsightItem;
use App\Services\PublicFileStorage;
use App\Support\GatedInsightKind;
use Illuminate\Http\Request;

class GatedInsightController extends Controller
{
    public function index(Request $request)
    {
        $meta = $this->meta();
        $query = GatedInsightItem::query()
            ->ofType($meta['type'])
            ->when(! empty($meta['gated']), fn ($q) => $q->withCount('downloads'))
            ->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(20)->withQueryString();

        return view('admin.gated-insights.index', compact('meta', 'items'));
    }

    public function create()
    {
        $meta = $this->meta();
        $item = new GatedInsightItem([
            'type' => $meta['type'],
            'status' => GatedInsightItem::STATUS_ACTIVE,
        ]);

        return view('admin.gated-insights.create', compact('meta', 'item'));
    }

    public function store(Request $request)
    {
        $meta = $this->meta();
        $data = $this->validated($request, true);
        $data['type'] = $meta['type'];
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), $meta['storage']);

        GatedInsightItem::create($data);

        return redirect()
            ->route($meta['admin'].'.index')
            ->with('success', $meta['singular'].' created successfully.');
    }

    public function edit(GatedInsightItem $item)
    {
        $meta = $this->meta();
        abort_unless($item->type === $meta['type'], 404);

        return view('admin.gated-insights.edit', compact('meta', 'item'));
    }

    public function update(Request $request, GatedInsightItem $item)
    {
        $meta = $this->meta();
        abort_unless($item->type === $meta['type'], 404);

        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($item->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), $meta['storage']);
        }

        $item->update($data);

        return redirect()
            ->route($meta['admin'].'.index')
            ->with('success', $meta['singular'].' updated successfully.');
    }

    public function destroy(GatedInsightItem $item)
    {
        $meta = $this->meta();
        abort_unless($item->type === $meta['type'], 404);

        PublicFileStorage::deleteByUrl($item->image);
        $item->delete();

        return redirect()
            ->route($meta['admin'].'.index')
            ->with('success', $meta['singular'].' deleted.');
    }

    public function downloads(Request $request)
    {
        $meta = $this->meta();
        abort_unless(! empty($meta['gated']), 404);

        $query = GatedInsightDownload::query()
            ->whereHas('item', fn ($q) => $q->ofType($meta['type']))
            ->with('item:id,title,slug,type')
            ->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhereHas('item', fn ($g) => $g->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('item_id')) {
            $query->where('gated_insight_item_id', $request->integer('item_id'));
        }

        $downloads = $query->paginate(30)->withQueryString();
        $items = GatedInsightItem::query()->ofType($meta['type'])->orderBy('title')->get(['id', 'title']);

        return view('admin.gated-insights.downloads', compact('meta', 'downloads', 'items'));
    }

    private function meta(): array
    {
        return GatedInsightKind::meta(GatedInsightKind::fromRouteName(request()->route()?->getName()));
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ]);

        unset($data['image']);

        return $data;
    }
}
