<?php

namespace App\Services;

use App\Models\MentorRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MentorRequestService
{
    public function create(User $mentee, int $mentorId, ?string $message = null): MentorRequest
    {
        if ($mentee->assigned_mentor_id && (int) $mentee->assigned_mentor_id === $mentorId) {
            throw new InvalidArgumentException('This mentor is already assigned to you.');
        }

        $mentor = User::where('id', $mentorId)
            ->where('role', 'mentor')
            ->where('is_active', true)
            ->first();

        if (! $mentor || ! $mentor->isApproved()) {
            throw new InvalidArgumentException('This mentor is not available for requests.');
        }

        $existingPending = MentorRequest::where('mentee_id', $mentee->id)
            ->where('mentor_id', $mentorId)
            ->where('status', MentorRequest::STATUS_PENDING)
            ->exists();

        if ($existingPending) {
            throw new InvalidArgumentException('You already have a pending request to this mentor.');
        }

        $request = MentorRequest::create([
            'mentee_id' => $mentee->id,
            'mentor_id' => $mentorId,
            'message'   => $message,
            'status'    => MentorRequest::STATUS_PENDING,
        ])->load('mentor');

        app(NotificationService::class)->send(
            $mentor,
            'mentor_request',
            'New mentee request',
            $mentee->name.' asked you to be their mentor.',
            ['mentor_request_id' => $request->id, 'mentee_id' => $mentee->id],
            app(NotificationService::class)->roleUrl($mentor, 'requests')
        );

        return $request;
    }

    public function cancel(User $mentee, MentorRequest $request): void
    {
        if ((int) $request->mentee_id !== (int) $mentee->id) {
            throw new InvalidArgumentException('Request not found.');
        }

        if (! $request->isPending()) {
            throw new InvalidArgumentException('Only pending requests can be cancelled.');
        }

        $request->update([
            'status'       => MentorRequest::STATUS_CANCELLED,
            'responded_at' => now(),
        ]);
    }

    public function accept(User $mentor, MentorRequest $request): MentorRequest
    {
        if ((int) $request->mentor_id !== (int) $mentor->id) {
            throw new InvalidArgumentException('Request not found.');
        }

        if (! $request->isPending()) {
            throw new InvalidArgumentException('This request is no longer pending.');
        }

        $mentee = User::where('id', $request->mentee_id)
            ->where('role', 'mentee')
            ->firstOrFail();

        DB::transaction(function () use ($request, $mentee, $mentor) {
            $request->update([
                'status'       => MentorRequest::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            $mentee->update(['assigned_mentor_id' => $mentor->id]);

            MentorRequest::where('mentee_id', $mentee->id)
                ->where('id', '!=', $request->id)
                ->where('status', MentorRequest::STATUS_PENDING)
                ->update([
                    'status'       => MentorRequest::STATUS_REJECTED,
                    'mentor_note'  => 'Mentee accepted another mentor.',
                    'responded_at' => now(),
                ]);
        });

        $fresh = $request->fresh()->load('mentee');

        app(NotificationService::class)->send(
            $mentee,
            'mentor_request_accepted',
            'Mentor request accepted',
            $mentor->name.' accepted your mentorship request.',
            ['mentor_request_id' => $request->id, 'mentor_id' => $mentor->id],
            app(NotificationService::class)->roleUrl($mentee, 'mentor.change')
        );

        return $fresh;
    }

    public function reject(User $mentor, MentorRequest $request, ?string $note = null): MentorRequest
    {
        if ((int) $request->mentor_id !== (int) $mentor->id) {
            throw new InvalidArgumentException('Request not found.');
        }

        if (! $request->isPending()) {
            throw new InvalidArgumentException('This request is no longer pending.');
        }

        $request->update([
            'status'       => MentorRequest::STATUS_REJECTED,
            'mentor_note'  => $note,
            'responded_at' => now(),
        ]);

        $fresh = $request->fresh()->load('mentee');
        $mentee = User::query()->find($request->mentee_id);

        if ($mentee) {
            app(NotificationService::class)->send(
                $mentee,
                'mentor_request_rejected',
                'Mentor request declined',
                $mentor->name.' declined your mentorship request.',
                ['mentor_request_id' => $request->id, 'mentor_id' => $mentor->id],
                app(NotificationService::class)->roleUrl($mentee, 'mentor.change')
            );
        }

        return $fresh;
    }
}
