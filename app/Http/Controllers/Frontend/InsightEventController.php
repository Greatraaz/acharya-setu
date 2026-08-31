<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\InsightEvent;
use App\Models\InsightEventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsightEventController extends Controller
{
    public function webinarsIndex(Request $request): View
    {
        return $this->index($request, InsightEvent::TYPE_WEBINAR);
    }

    public function eventsIndex(Request $request): View
    {
        return $this->index($request, InsightEvent::TYPE_EVENT);
    }

    public function webinarsShow(string $slug): View
    {
        return $this->show($slug, InsightEvent::TYPE_WEBINAR);
    }

    public function eventsShow(string $slug): View
    {
        return $this->show($slug, InsightEvent::TYPE_EVENT);
    }

    public function webinarsRegister(Request $request, string $slug): RedirectResponse
    {
        return $this->register($request, $slug, InsightEvent::TYPE_WEBINAR);
    }

    public function eventsRegister(Request $request, string $slug): RedirectResponse
    {
        return $this->register($request, $slug, InsightEvent::TYPE_EVENT);
    }

    private function index(Request $request, string $type): View
    {
        $filter = $request->input('filter', 'all');
        if (! in_array($filter, ['all', 'upcoming', 'past'], true)) {
            $filter = 'all';
        }

        $query = InsightEvent::query()
            ->active()
            ->ofType($type)
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        if ($filter === 'upcoming') {
            $query->upcoming();
        } elseif ($filter === 'past') {
            $query->past();
        }

        $sessions = $query->paginate(9)->withQueryString();

        $view = $type === InsightEvent::TYPE_WEBINAR
            ? 'frontend.insights.webinars.index'
            : 'frontend.insights.events.index';

        return view($view, compact('sessions', 'filter', 'type'));
    }

    private function show(string $slug, string $type): View
    {
        $session = InsightEvent::query()
            ->active()
            ->ofType($type)
            ->where('slug', $slug)
            ->firstOrFail();

        $recent = InsightEvent::query()
            ->active()
            ->ofType($type)
            ->where('id', '!=', $session->id)
            ->latest('start_date')
            ->limit(4)
            ->get();

        return view('frontend.insights.partials.session-show', compact('session', 'recent', 'type'));
    }

    private function register(Request $request, string $slug, string $type): RedirectResponse
    {
        $session = InsightEvent::query()
            ->active()
            ->ofType($type)
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        InsightEventRegistration::create([
            ...$data,
            'insight_event_id' => $session->id,
            'user_id' => auth()->id(),
        ]);

        $route = $type === InsightEvent::TYPE_WEBINAR
            ? 'insights.webinars.show'
            : 'insights.events.show';

        return redirect()
            ->route($route, $session->slug)
            ->with('registration_success', true);
    }
}
