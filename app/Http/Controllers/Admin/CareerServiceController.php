<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerServiceRequest;
use App\Services\CareerServiceService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CareerServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $type = trim((string) $request->input('type', ''));

        $items = CareerServiceRequest::with('user')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('id', $search)
                        ->orWhere('linkedin_url', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.career-services.index', compact('items', 'search', 'status', 'type'));
    }

    public function show(CareerServiceRequest $careerService)
    {
        $careerService->load(['user', 'reviewer', 'invoice']);

        if (in_array($careerService->payment_status, ['paid', 'free'], true) && ! $careerService->invoice) {
            app(\App\Services\CareerServiceInvoiceService::class)->ensureForRequest($careerService, 'admin');
            $careerService->load('invoice');
        }

        return view('admin.career-services.show', ['item' => $careerService]);
    }

    public function complete(Request $request, CareerServiceRequest $careerService, CareerServiceService $services)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
            'deliverable' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            $services->complete($careerService, $request->user(), [
                'admin_notes' => $request->input('admin_notes'),
                'deliverable' => $request->file('deliverable'),
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.career-services.show', $careerService)
            ->with('success', 'Deliverable sent to the mentee.');
    }
}
