<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MockInterviewRequest;
use App\Services\AgoraService;
use App\Services\MockInterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MockInterviewController extends Controller
{
    public function options(Request $request, MockInterviewService $services): JsonResponse
    {
        $duration = $request->query('duration');
        $durationMinutes = $duration !== null && $duration !== '' ? (int) $duration : null;

        try {
            $quote = $services->quote($request->user(), $durationMinutes);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Mock interview options.',
            'data'    => [
                'prices' => $services->prices(),
                'quote'  => $quote,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = MockInterviewRequest::query()
            ->with(['assigner', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status'  => true,
            'message' => 'Requests fetched.',
            'data'    => $items->getCollection()->map->toPublicArray()->values(),
            'meta'    => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $item = MockInterviewRequest::query()
            ->with('assigner')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Request fetched.',
            'data'    => $item->toPublicArray(),
        ]);
    }

    public function agoraToken(Request $request, $id, AgoraService $agora): JsonResponse
    {
        $user = $request->user();
        $item = MockInterviewRequest::query()
            ->with(['assigner:id,name,avatar_url', 'user:id,name,avatar_url'])
            ->where('id', $id)
            ->first();

        if (! $item || ! $item->isParticipant($user)) {
            return response()->json(['status' => false, 'statuscode' => 404, 'message' => 'Request not found.'], 404);
        }

        try {
            $payload = $agora->issueMockInterviewToken($user, $item);
        } catch (HttpException $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => $e->getStatusCode(),
                'message'    => $e->getMessage(),
            ], $e->getStatusCode());
        }

        return response()->json(array_merge([
            'status'     => true,
            'statuscode' => 200,
        ], $payload));
    }

    public function store(Request $request, MockInterviewService $services): JsonResponse
    {
        $data = $request->validate([
            'duration_minutes' => 'required|in:30,45,60,90',
            'preferred_at'     => 'required|date',
            'timezone'         => 'nullable|string|max:64',
            'target_role'      => 'nullable|string|max:255',
            'mentee_notes'     => 'nullable|string|max:2000',
            'payment_method'   => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->submit($request->user(), [
                'duration_minutes' => (int) $data['duration_minutes'],
                'preferred_at'     => $data['preferred_at'],
                'timezone'         => $data['timezone'] ?? null,
                'target_role'      => $data['target_role'] ?? null,
                'mentee_notes'     => $data['mentee_notes'] ?? null,
                'payment_method'   => $data['payment_method'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return $this->paymentJson($result);
    }

    public function pay(Request $request, $id, MockInterviewService $services): JsonResponse
    {
        $item = MockInterviewRequest::withoutGlobalScopes()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        $data = $request->validate([
            'payment_method' => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        try {
            $result = $services->pay($request->user(), $item, $data['payment_method'] ?? null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return $this->paymentJson($result);
    }

    public function verify(Request $request, $id, MockInterviewService $services): JsonResponse
    {
        $item = MockInterviewRequest::withoutGlobalScopes()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        $payload = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        try {
            $item = $services->verifyPayment($request->user(), $item, $payload);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Payment successful. Your request is now under review.',
            'data'    => $item->toPublicArray(),
        ]);
    }

    /**
     * Personal notes (private).
     * GET /api/v1/mentee/mock-interviews/{id}/notes
     */
    public function notes(Request $request, int $id): JsonResponse
    {
        $item = $this->findOwned($request, $id);
        $userId = (int) $request->user()->id;

        $notes = $item->notes()
            ->with('author:id,name,role,avatar_url')
            ->where('author_id', $userId)
            ->where('is_shared', false)
            ->latest()
            ->get()
            ->map(fn ($note) => $this->formatNote($note));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mock_interview_id' => $item->id,
            'count'      => $notes->count(),
            'my_note'    => $notes->first(),
            'notes'      => $notes,
        ]);
    }

    /**
     * Create / upsert personal note.
     * POST /api/v1/mentee/mock-interviews/{id}/notes
     */
    public function addNote(Request $request, int $id): JsonResponse
    {
        $item = $this->findOwned($request, $id);
        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'content' => 'required|string|max:65535',
        ]);

        $note = $item->notes()
            ->where('author_id', $userId)
            ->where('is_shared', false)
            ->where('type', 'note')
            ->latest()
            ->first();

        if ($note) {
            $note->update(['content' => $data['content'], 'resource_url' => null]);
            $statusCode = 200;
            $message = 'Personal note updated.';
        } else {
            $note = $item->notes()->create([
                'author_id'    => $userId,
                'type'         => 'note',
                'content'      => $data['content'],
                'resource_url' => null,
                'is_shared'    => false,
            ]);
            $statusCode = 201;
            $message = 'Personal note saved.';
        }

        $note->load('author:id,name,role,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => $statusCode,
            'message'    => $message,
            'note'       => $this->formatNote($note),
        ], $statusCode);
    }

    public function updateNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $item = $this->findOwned($request, $id);

        $note = $item->notes()
            ->where('id', $noteId)
            ->where('author_id', $request->user()->id)
            ->where('is_shared', false)
            ->firstOrFail();

        $data = $request->validate([
            'content' => 'required|string|max:65535',
        ]);

        $note->update(['content' => $data['content']]);
        $note->load('author:id,name,role,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Personal note updated.',
            'note'       => $this->formatNote($note),
        ]);
    }

    public function destroyNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $item = $this->findOwned($request, $id);

        $note = $item->notes()
            ->where('id', $noteId)
            ->where('author_id', $request->user()->id)
            ->where('is_shared', false)
            ->firstOrFail();

        $note->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Personal note deleted.',
        ]);
    }

    /**
     * Shared notes visible to mentee + interviewer/admin.
     * GET /api/v1/mentee/mock-interviews/{id}/shared-notes
     */
    public function sharedNotes(Request $request, int $id): JsonResponse
    {
        $item = $this->findOwned($request, $id);
        $userId = (int) $request->user()->id;

        $notes = $item->notes()
            ->with('author:id,name,role,avatar_url')
            ->where('is_shared', true)
            ->latest()
            ->get()
            ->map(fn ($note) => $this->formatNote($note));

        $mySharedNote = $notes->first(fn ($n) => (int) $n['author_id'] === $userId);

        return response()->json([
            'status'            => true,
            'statuscode'        => 200,
            'mock_interview_id' => $item->id,
            'count'             => $notes->count(),
            'my_shared_note'    => $mySharedNote,
            'notes'             => $notes,
        ]);
    }

    public function saveSharedNote(Request $request, int $id): JsonResponse
    {
        $item = $this->findOwned($request, $id);
        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'content' => 'required|string|max:65535',
            'type'    => 'nullable|in:note,resource,action_item',
        ]);

        $type = $data['type'] ?? 'note';

        $note = $item->notes()
            ->where('author_id', $userId)
            ->where('type', $type)
            ->where('is_shared', true)
            ->latest()
            ->first();

        $payload = [
            'content'   => $data['content'],
            'is_shared' => true,
            'type'      => $type,
        ];

        if ($note) {
            $note->update($payload);
            $statusCode = 200;
        } else {
            $note = $item->notes()->create(array_merge($payload, [
                'author_id'    => $userId,
                'resource_url' => null,
            ]));
            $statusCode = 201;
        }

        $note->load('author:id,name,role,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => $statusCode,
            'message'    => 'Shared notes saved.',
            'note'       => $this->formatNote($note),
        ], $statusCode);
    }

    public function updateSharedNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $item = $this->findOwned($request, $id);

        $note = $item->notes()
            ->where('id', $noteId)
            ->where('author_id', $request->user()->id)
            ->where('is_shared', true)
            ->firstOrFail();

        $data = $request->validate([
            'content' => 'required|string|max:65535',
            'type'    => 'nullable|in:note,resource,action_item',
        ]);

        $note->update(array_filter([
            'content' => $data['content'],
            'type'    => $data['type'] ?? null,
        ], fn ($v) => $v !== null));

        $note->load('author:id,name,role,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Shared note updated.',
            'note'       => $this->formatNote($note),
        ]);
    }

    public function destroySharedNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $item = $this->findOwned($request, $id);

        $note = $item->notes()
            ->where('id', $noteId)
            ->where('author_id', $request->user()->id)
            ->where('is_shared', true)
            ->firstOrFail();

        $note->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Shared note deleted.',
        ]);
    }

    private function findOwned(Request $request, int $id): MockInterviewRequest
    {
        return MockInterviewRequest::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function formatNote($note): array
    {
        return [
            'id'                        => $note->id,
            'mock_interview_request_id' => $note->mock_interview_request_id,
            'author_id'                 => $note->author_id,
            'type'                      => $note->type,
            'content'                   => $note->content,
            'resource_url'              => $note->resource_url,
            'is_shared'                 => (bool) $note->is_shared,
            'created_at'                => $note->created_at,
            'updated_at'                => $note->updated_at,
            'author'                    => $note->relationLoaded('author') && $note->author ? [
                'id'         => $note->author->id,
                'name'       => $note->author->name,
                'role'       => $note->author->role,
                'avatar_url' => $note->author->avatar_url,
            ] : null,
        ];
    }

    private function paymentJson(array $result): JsonResponse
    {
        $payment = $result['payment'] ?? null;
        $needsChoice = (bool) ($result['requires_payment_choice'] ?? false);
        $message = 'Request submitted. Our team will review it shortly.';
        if ($needsChoice) {
            $message = 'Choose a payment method to continue.';
        } elseif ($payment) {
            $message = 'Complete payment to submit your request.';
        }

        return response()->json([
            'status'  => true,
            'message' => $message,
            'requires_payment' => (bool) $payment,
            'requires_payment_choice' => $needsChoice,
            'data' => [
                'request' => $result['request']?->toPublicArray(),
                'payment' => $payment,
                'payment_choice' => $result['payment_choice'] ?? null,
            ],
        ], ($payment || $needsChoice) ? 201 : 200);
    }
}
