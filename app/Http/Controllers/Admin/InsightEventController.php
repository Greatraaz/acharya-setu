<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsightEvent;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;

class InsightEventController extends Controller
{
    public function index(Request $request)
    {
        $query = InsightEvent::query()->latest('start_date')->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('speaker', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->paginate(20)->withQueryString();

        return view('admin.events-webinars.index', compact('events'));
    }

    public function create()
    {
        $event = new InsightEvent([
            'status' => InsightEvent::STATUS_ACTIVE,
            'type' => InsightEvent::TYPE_WEBINAR,
        ]);

        return view('admin.events-webinars.create', compact('event'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'insight-events');

        InsightEvent::create($data);

        return redirect()
            ->route('admin.events-webinars.index')
            ->with('success', 'Entry created successfully.');
    }

    public function edit(InsightEvent $events_webinar)
    {
        $event = $events_webinar;

        return view('admin.events-webinars.edit', compact('event'));
    }

    public function update(Request $request, InsightEvent $events_webinar)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($events_webinar->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'insight-events');
        }

        $events_webinar->update($data);

        return redirect()
            ->route('admin.events-webinars.index')
            ->with('success', 'Entry updated successfully.');
    }

    public function destroy(InsightEvent $events_webinar)
    {
        PublicFileStorage::deleteByUrl($events_webinar->image);
        $events_webinar->delete();

        return redirect()
            ->route('admin.events-webinars.index')
            ->with('success', 'Entry deleted.');
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $rules = [
            'type' => 'required|in:webinar,event',
            'title' => 'required|string|max:255',
            'speaker' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'event_agenda' => 'nullable|string',
            'who_should_attend' => 'nullable|string',
            'what_you_will_learn' => 'nullable|string',
            'faq' => 'nullable|string|max:5000',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ];

        $data = $request->validate($rules);
        unset($data['image']);

        return $data;
    }
}
