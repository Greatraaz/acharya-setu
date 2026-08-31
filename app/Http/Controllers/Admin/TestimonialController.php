<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::query()->latest('id');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $testimonials = $query->paginate(20)->withQueryString();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        $testimonial = new Testimonial([
            'status' => Testimonial::STATUS_ACTIVE,
        ]);

        return view('admin.testimonials.create', compact('testimonial'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['created_by'] = auth()->id();
        $data['image'] = PublicFileStorage::store($request->file('image'), 'testimonials');

        Testimonial::create($data);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $this->validated($request, false);

        if ($request->hasFile('image')) {
            PublicFileStorage::deleteByUrl($testimonial->image);
            $data['image'] = PublicFileStorage::store($request->file('image'), 'testimonials');
        }

        $testimonial->update($data);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        PublicFileStorage::deleteByUrl($testimonial->image);
        $testimonial->delete();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted.');
    }

    private function validated(Request $request, bool $requireImage): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'message' => 'required|string',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
        ];

        $data = $request->validate($rules);
        unset($data['image']);

        return $data;
    }
}
