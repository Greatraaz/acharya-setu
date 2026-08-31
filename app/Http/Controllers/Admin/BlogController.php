<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\PublicFileStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::query()->latest('blog_date')->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $blogs = $query->paginate(20)->withQueryString();

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create()
    {
        $blog = new Blog([
            'status' => Blog::STATUS_ACTIVE,
            'blog_date' => now()->toDateString(),
            'author' => auth()->user()->name ?? 'Vedrix',
        ]);

        return view('admin.blogs.create', compact('blog'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'blogs');

        Blog::create($data);

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog created successfully.');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($blog->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'blogs');
        }

        $blog->update($data);

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog updated successfully.');
    }

    public function destroy(Blog $blog)
    {
        PublicFileStorage::deleteByUrl($blog->image);
        $blog->delete();

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog deleted.');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $blogs = $this->filteredBlogs($request)->get();
        $filename = 'blogs-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($blogs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Sr. No.', 'Title', 'Category', 'Author', 'Blog Date', 'Status']);
            foreach ($blogs as $i => $blog) {
                fputcsv($out, [
                    $i + 1,
                    $blog->title,
                    $blog->category,
                    $blog->author,
                    optional($blog->blog_date)->format('Y-m-d'),
                    ucfirst($blog->status),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $blogs = $this->filteredBlogs($request)->get();
        $pdf = Pdf::loadView('admin.blogs.export-pdf', compact('blogs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('blogs-'.now()->format('Y-m-d-His').'.pdf');
    }

    private function filteredBlogs(Request $request)
    {
        $query = Blog::query()->latest('blog_date')->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:120',
            'author' => 'nullable|string|max:120',
            'blog_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
            'description' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:500',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ];

        $data = $request->validate($rules);
        unset($data['image']);

        return $data;
    }
}
