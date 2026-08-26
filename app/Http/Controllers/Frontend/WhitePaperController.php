<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WhitePaper;
use App\Services\PublicFileStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class WhitePaperController extends Controller
{
    public function index(Request $request): View
    {
        $whitePapers = WhitePaper::query()
            ->where('status', WhitePaper::STATUS_ACTIVE)
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('frontend.insights.white-papers.index', compact('whitePapers'));
    }

    public function download(string $slug): SymfonyResponse
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

            $pdf = Pdf::loadView('frontend.insights.white-papers.download-pdf', [
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

    private function htmlFallback(WhitePaper $paper, ?string $coverDataUri, string $descriptionHtml): Response
    {
        return response()
            ->view('frontend.insights.white-papers.download-pdf', [
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

        // Embed local /storage images as data URIs so DomPDF works on shared hosting.
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
