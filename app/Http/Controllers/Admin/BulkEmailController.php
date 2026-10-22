<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminBulkEmailJob;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\BulkEmailAudience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BulkEmailController extends Controller
{
    public function create(): View
    {
        $counts = [
            'all' => BulkEmailAudience::query(BulkEmailAudience::ALL)->count(),
            'mentors' => BulkEmailAudience::query(BulkEmailAudience::MENTORS)->count(),
            'mentees' => BulkEmailAudience::query(BulkEmailAudience::MENTEES)->count(),
        ];

        $recent = ActivityLog::query()
            ->where('event', 'bulk_email_sent')
            ->latest('logged_at')
            ->limit(8)
            ->get();

        return view('admin.bulk-emails.create', compact('counts', 'recent'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'audience' => ['required', 'in:'.implode(',', BulkEmailAudience::options())],
            'user_ids' => ['nullable', 'array', 'max:500'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:20000'],
        ], [
            'audience.required' => 'Choose who should receive this email.',
            'subject.required' => 'Enter an email subject.',
            'body.required' => 'Enter the email message.',
        ]);

        $audience = $data['audience'];
        $userIds = array_values(array_unique(array_map('intval', $data['user_ids'] ?? [])));

        if ($audience === BulkEmailAudience::SPECIFIC && $userIds === []) {
            return back()
                ->withInput()
                ->withErrors(['user_ids' => 'Select at least one user to email.']);
        }

        $recipientIds = BulkEmailAudience::recipientIds($audience, $userIds);

        if ($recipientIds === []) {
            return back()
                ->withInput()
                ->withErrors(['audience' => 'No users with a valid email match this selection.']);
        }

        $subject = trim($data['subject']);
        $body = trim($data['body']);

        foreach (array_chunk($recipientIds, 40) as $chunk) {
            SendAdminBulkEmailJob::dispatchAfterResponse($chunk, $subject, $body);
        }

        $count = count($recipientIds);
        $label = BulkEmailAudience::label($audience);

        (new ActivityLogger())
            ->event('bulk_email_sent')
            ->module('communication')
            ->success()
            ->description('Bulk email queued for '.$count.' '.$label.'.')
            ->withMeta([
                'audience' => $audience,
                'recipient_count' => $count,
                'subject' => $subject,
            ])
            ->log();

        return redirect()
            ->route('admin.bulk-emails.create')
            ->with('success', 'Email is being sent to '.$count.' '.$label.'. Each person receives their own copy.');
    }

    public function preview(Request $request): JsonResponse
    {
        $audience = (string) $request->query('audience', BulkEmailAudience::ALL);
        if (! in_array($audience, BulkEmailAudience::options(), true)) {
            $audience = BulkEmailAudience::ALL;
        }

        $userIds = array_values(array_filter(array_map('intval', (array) $request->query('user_ids', []))));
        $count = BulkEmailAudience::query($audience, $userIds)->count();

        return response()->json([
            'count' => $count,
            'label' => BulkEmailAudience::label($audience),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');

        $query = User::query()
            ->select('id', 'name', 'email', 'role')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereIn('role', ['mentor', 'mentee']);

        if (in_array($role, ['mentor', 'mentee'], true)) {
            $query->where('role', $role);
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        return response()->json(
            $query->orderBy('name')->limit(40)->get()
        );
    }
}
