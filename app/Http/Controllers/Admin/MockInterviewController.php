<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MockInterviewRequest;
use App\Services\MockInterviewService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MockInterviewController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));

        $items = MockInterviewRequest::with(['user', 'assigner'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('id', $search)
                        ->orWhere('target_role', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.mock-interviews.index', compact('items', 'search', 'status'));
    }

    public function show(MockInterviewRequest $mockInterview)
    {
        $mockInterview->load([
            'user',
            'reviewer',
            'assigner',
            'notes.author:id,name,role,avatar_url',
        ]);

        return view('admin.mock-interviews.show', [
            'item' => $mockInterview,
        ]);
    }

    public function saveSharedNote(Request $request, MockInterviewRequest $mockInterview)
    {
        $data = $request->validate([
            'content'   => 'required|string|max:65535',
            'type'      => 'nullable|in:note,resource,action_item',
            'is_shared' => 'nullable|boolean',
        ]);

        $type = $data['type'] ?? 'note';
        $payload = [
            'content'   => $data['content'],
            'is_shared' => $request->boolean('is_shared', true),
            'type'      => $type,
        ];

        $note = $mockInterview->notes()
            ->where('author_id', auth()->id())
            ->where('type', $type)
            ->where('is_shared', true)
            ->latest()
            ->first();

        if ($note) {
            $note->update($payload);
        } else {
            $note = $mockInterview->notes()->create(array_merge($payload, [
                'author_id' => auth()->id(),
            ]));
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Shared notes saved.', 'note' => $note]);
        }

        return redirect()
            ->route('admin.mock-interviews.show', $mockInterview)
            ->with('success', 'Shared notes saved.');
    }

    public function confirm(Request $request, MockInterviewRequest $mockInterview, MockInterviewService $services)
    {
        $data = $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        try {
            $services->confirm(
                $mockInterview,
                $request->user(),
                $data['admin_notes'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.mock-interviews.show', $mockInterview)
            ->with('success', 'Mock interview confirmed.');
    }

    public function complete(Request $request, MockInterviewRequest $mockInterview, MockInterviewService $services)
    {
        $data = $request->validate([
            'feedback'    => 'required|string|max:5000',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        try {
            $item = $services->complete($mockInterview, $request->user(), $data['feedback']);
            if (! empty($data['admin_notes'])) {
                $item->update(['admin_notes' => trim($data['admin_notes'])]);
            }
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.mock-interviews.show', $mockInterview)
            ->with('success', 'Feedback sent to the mentee.');
    }

    public function cancel(Request $request, MockInterviewRequest $mockInterview, MockInterviewService $services)
    {
        $data = $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        try {
            $item = $services->cancel($mockInterview);
            if (! empty($data['admin_notes'])) {
                $item->update(['admin_notes' => trim($data['admin_notes'])]);
            }
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.mock-interviews.show', $mockInterview)
            ->with('success', 'Request cancelled.');
    }
}
