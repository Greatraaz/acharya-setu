<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\DownloadCentre;
use App\Models\InsightEvent;
use App\Models\InsightEventRegistration;
use App\Models\InsightVideo;
use App\Models\Podcast;
use App\Models\Testimonial;
use App\Models\WhitePaper;
use App\Services\PublicFileStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class InsightController extends Controller
{
    /**
     * Explore all insights hub overview page.
     */
    public function exploreAll(Request $request): View
    {
        $blogs = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->latest('blog_date')
            ->latest('id')
            ->limit(6)
            ->get();

        $stats = [
            'blogs' => Blog::where('status', Blog::STATUS_ACTIVE)->count(),
            'white_papers' => WhitePaper::where('status', WhitePaper::STATUS_ACTIVE)->count(),
            'case_studies' => CaseStudy::where('status', CaseStudy::STATUS_ACTIVE)->count(),
            'podcasts' => Podcast::where('status', Podcast::STATUS_ACTIVE)->count(),
            'webinars' => InsightEvent::where('status', InsightEvent::STATUS_ACTIVE)->where('type', InsightEvent::TYPE_WEBINAR)->count(),
            'videos' => InsightVideo::where('status', InsightVideo::STATUS_ACTIVE)->count(),
            'downloads' => DownloadCentre::where('status', DownloadCentre::STATUS_ACTIVE)->count(),
            'testimonials' => Testimonial::where('status', Testimonial::STATUS_ACTIVE)->count(),
        ];

        return view('frontend.insights.exploreAllInsights', compact('blogs', 'stats'));
    }

    // =========================================================================
    // BLOGS
    // =========================================================================

    public function blogs(Request $request): View
    {
        $category = trim((string) $request->query('category', ''));

        $query = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->latest('blog_date')
            ->latest('id');

        if ($category !== '') {
            $query->where('category', $category);
        }

        $blogs = $query->paginate(9)->withQueryString();

        $categories = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('frontend.insights.blogs', compact('blogs', 'categories', 'category'));
    }

    public function blogSingle(string $slug): View
    {
        $blog = Blog::query()
            ->where('slug', $slug)
            ->where('status', Blog::STATUS_ACTIVE)
            ->firstOrFail();

        $recent = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->where('id', '!=', $blog->id)
            ->latest('blog_date')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('frontend.insights.blogSingle', compact('blog', 'recent'));
    }

    // =========================================================================
    // WHITE PAPERS
    // =========================================================================

    public function whitePapers(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $whitePapers = WhitePaper::query()
            ->where('status', WhitePaper::STATUS_ACTIVE)
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.whitePapers', compact('whitePapers', 'search'));
    }

    public function whitePaperDownload(string $slug): SymfonyResponse
    {
        $paper = WhitePaper::query()
            ->where('slug', $slug)
            ->where('status', WhitePaper::STATUS_ACTIVE)
            ->firstOrFail();

        $coverDataUri = $this->localImageDataUri($paper->image);
        $description = $this->prepareDescriptionHtml((string) $paper->description);

        try {
            if (! class_exists(Pdf::class)) {
                return $this->htmlFallback($paper, $coverDataUri, $description);
            }

            $pdf = Pdf::loadView('frontend.insights.whitePaperDownloadPdf', [
                'paper' => $paper,
                'coverDataUri' => $coverDataUri,
                'descriptionHtml' => $description,
            ])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                ]);

            return $pdf->download($paper->downloadFilename());
        } catch (Throwable $e) {
            report($e);

            return $this->htmlFallback($paper, $coverDataUri, $description);
        }
    }

    // =========================================================================
    // CASE STUDIES
    // =========================================================================

    public function caseStudies(Request $request): View
    {
        $industry = trim((string) $request->input('industry', ''));
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $caseStudies = CaseStudy::query()
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->when($industry !== '', fn ($q) => $q->where('industry', $industry))
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('industry', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        $industries = CaseStudy::query()
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->whereNotNull('industry')
            ->where('industry', '!=', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry');

        return view('frontend.insights.caseStudies', compact('caseStudies', 'industries', 'industry', 'search'));
    }

    public function caseStudySingle(string $slug): View
    {
        $caseStudy = CaseStudy::query()
            ->where('slug', $slug)
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->firstOrFail();

        $recent = CaseStudy::query()
            ->where('status', CaseStudy::STATUS_ACTIVE)
            ->where('id', '!=', $caseStudy->id)
            ->latest('id')
            ->limit(4)
            ->get();

        return view('frontend.insights.caseStudySingle', compact('caseStudy', 'recent'));
    }

    // =========================================================================
    // PODCASTS
    // =========================================================================

    public function podcasts(Request $request): View
    {
        $type = $request->input('type', '');
        if (! in_array($type, ['', Podcast::TYPE_AUDIO, Podcast::TYPE_YOUTUBE], true)) {
            $type = '';
        }

        $query = Podcast::query()
            ->active()
            ->latest('id');

        if ($type !== '') {
            $query->where('podcast_type', $type);
        }

        $podcasts = $query->paginate(9)->withQueryString();

        return view('frontend.insights.podcasts', compact('podcasts', 'type'));
    }

    // =========================================================================
    // VIDEOS
    // =========================================================================

    public function videos(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $videos = InsightVideo::query()
            ->active()
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.videos', compact('videos', 'search'));
    }

    // =========================================================================
    // WEBINARS & EVENTS
    // =========================================================================

    public function webinars(Request $request): View
    {
        return $this->renderEventsIndex($request, InsightEvent::TYPE_WEBINAR);
    }

    public function events(Request $request): View
    {
        return $this->renderEventsIndex($request, InsightEvent::TYPE_EVENT);
    }

    public function webinarSingle(string $slug): View
    {
        return $this->renderEventsShow($slug, InsightEvent::TYPE_WEBINAR);
    }

    public function eventSingle(string $slug): View
    {
        return $this->renderEventsShow($slug, InsightEvent::TYPE_EVENT);
    }

    public function webinarRegister(Request $request, string $slug): RedirectResponse
    {
        return $this->processEventRegistration($request, $slug, InsightEvent::TYPE_WEBINAR);
    }

    public function eventRegister(Request $request, string $slug): RedirectResponse
    {
        return $this->processEventRegistration($request, $slug, InsightEvent::TYPE_EVENT);
    }

    // =========================================================================
    // DOWNLOAD CENTRE
    // =========================================================================

    public function downloadCentre(Request $request): View
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

        return view('frontend.insights.downloadCentre', compact('downloads', 'search'));
    }

    public function downloadCentreDownload(string $slug): BinaryFileResponse
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

    // =========================================================================
    // TESTIMONIALS & LEARNER SUCCESS STORIES
    // =========================================================================

    public function testimonials(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $testimonials = Testimonial::query()
            ->active()
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('designation', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.testimonials', compact('testimonials', 'search'));
    }

    public function learnerSuccessStories(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $testimonials = Testimonial::query()
            ->active()
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('designation', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            }))
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.learnerSuccessStories', compact('testimonials', 'search'));
    }

    // =========================================================================
    // THEMES & TOPICS (CAREER GUIDES, MENTORSHIP GUIDES, INDUSTRY REPORTS, MEDIA)
    // =========================================================================

    public function careerGuides(Request $request): View
    {
        $blogs = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->latest('blog_date')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.careerGuides', compact('blogs'));
    }

    public function mentorshipGuides(Request $request): View
    {
        $blogs = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->latest('blog_date')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.mentorshipGuides', compact('blogs'));
    }

    public function industryReports(Request $request): View
    {
        $whitePapers = WhitePaper::query()
            ->where('status', WhitePaper::STATUS_ACTIVE)
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.industryReports', compact('whitePapers'));
    }

    public function curatedEssaysAndMedia(Request $request): View
    {
        $blogs = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->latest('blog_date')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('frontend.insights.curatedEssaysAndMedia', compact('blogs'));
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function renderEventsIndex(Request $request, string $type): View
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
            ? 'frontend.insights.webinars'
            : 'frontend.insights.events';

        return view($view, compact('sessions', 'filter', 'type'));
    }

    private function renderEventsShow(string $slug, string $type): View
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

        return view('frontend.insights.sessionSingle', compact('session', 'recent', 'type'));
    }

    private function processEventRegistration(Request $request, string $slug, string $type): RedirectResponse
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

    private function htmlFallback(WhitePaper $paper, ?string $coverDataUri, string $descriptionHtml): Response
    {
        return response()
            ->view('frontend.insights.whitePaperDownloadPdf', [
                'paper' => $paper,
                'coverDataUri' => $coverDataUri,
                'descriptionHtml' => $descriptionHtml,
            ])
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'inline; filename="'.$paper->downloadFilename().'"');
    }

    private function prepareDescriptionHtml(string $html): string
    {
        if ($html === '') {
            return '<p><em>No description provided.</em></p>';
        }

        return preg_replace_callback(
            '/<img\b([^>]*?)src=["\']([^"\']+)["\']([^>]*)>/i',
            function (array $matches): string {
                $src = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
                $dataUri = $this->resolveImageDataUri($src);

                if (! $dataUri) {
                    return '';
                }

                return '<img'.$matches[1].'src="'.$dataUri.'"'.$matches[3].'>';
            },
            $html
        ) ?? $html;
    }

    private function resolveImageDataUri(string $src): ?string
    {
        if (str_starts_with($src, 'data:')) {
            return $src;
        }

        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            $path = PublicFileStorage::pathFromUrl($src);
            if ($path) {
                return $this->localImageDataUri($path);
            }

            return $src;
        }

        $relative = PublicFileStorage::pathFromUrl($src) ?? ltrim($src, '/');

        return $this->localImageDataUri($relative);
    }

    private function localImageDataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $relative = PublicFileStorage::pathFromUrl($path) ?? ltrim($path, '/');
        $abs = storage_path('app/public/'.ltrim($relative, '/'));

        if (! is_file($abs) || ! is_readable($abs)) {
            return null;
        }

        $mime = @mime_content_type($abs) ?: 'image/jpeg';
        $data = @file_get_contents($abs);

        if ($data === false || $data === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }
}
