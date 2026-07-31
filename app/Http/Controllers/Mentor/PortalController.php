<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Channel;
use App\Models\ConsultationSession;
use App\Models\MenteeEnrollment;
use App\Models\SessionNote;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PortalController extends Controller
{
    public function notes()
    {
        $mentorId = auth()->id();

        $notes = SessionNote::query()
            ->whereHas('session', fn ($q) => $q->where('mentor_id', $mentorId))
            ->with(['session.mentee', 'author'])
            ->latest()
            ->paginate(20);

        $sessionsWithoutNotes = ConsultationSession::where('mentor_id', $mentorId)
            ->whereIn('status', ['completed', 'confirmed'])
            ->whereDoesntHave('notes')
            ->with('mentee')
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        return view('frontend.mentors.notes', compact('notes', 'sessionsWithoutNotes'));
    }

    public function mentees()
    {
        $mentees = $this->mentorMenteesQuery()->paginate(20);

        return view('frontend.mentors.mentees', compact('mentees'));
    }

    public function menteeShow(int $id)
    {
        $mentee = $this->findMentorMentee($id);
        $mentorId = auth()->id();

        $sessions = ConsultationSession::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->latest('scheduled_at')
            ->limit(15)
            ->get();

        $enrollments = MenteeEnrollment::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->with('stream')
            ->get();

        return view('frontend.mentors.mentee-show', compact('mentee', 'sessions', 'enrollments'));
    }

    public function journey()
    {
        $mentorId = auth()->id();

        $enrollments = MenteeEnrollment::where('mentor_id', $mentorId)
            ->with(['mentee', 'stream'])
            ->latest()
            ->get()
            ->map(function (MenteeEnrollment $enrollment) {
                $enrollment->progress_data = $enrollment->progress;
                return $enrollment;
            });

        $menteesWithoutEnrollment = $this->mentorMenteesQuery()
            ->whereDoesntHave('enrollments', fn ($q) => $q->where('mentor_id', $mentorId))
            ->limit(20)
            ->get();

        return view('frontend.mentors.journey', compact('enrollments', 'menteesWithoutEnrollment'));
    }

    public function journeyShow(int $menteeId)
    {
        $mentee = $this->findMentorMentee($menteeId);
        $mentorId = auth()->id();

        $enrollments = MenteeEnrollment::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->with('stream')
            ->get()
            ->map(function (MenteeEnrollment $enrollment) {
                $enrollment->progress_data = $enrollment->progress;
                return $enrollment;
            });

        return view('frontend.mentors.journey-show', compact('mentee', 'enrollments'));
    }

    public function community()
    {
        $user = auth()->user();

        $channels = Channel::query()
            ->where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where('type', Channel::TYPE_PUBLIC)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id));
            })
            ->withCount(['allMessages', 'members'])
            ->with('creator:id,name')
            ->latest()
            ->get()
            ->map(function (Channel $ch) use ($user) {
                $ch->unread_count = $ch->isMember($user) ? $ch->unreadCountFor($user) : 0;
                return $ch;
            });

        return view('frontend.mentors.community', compact('channels'));
    }

    public function communityShow(Channel $channel)
    {
        $user = auth()->user();
        abort_unless($channel->canAccess($user), 403);

        if ($channel->isMember($user)) {
            $channel->markRead($user);
        }

        $messages = $channel->messages()
            ->with(['user:id,name,avatar_url,role', 'replies.user:id,name,avatar_url'])
            ->latest()
            ->paginate(30);

        return view('frontend.mentors.community-show', compact('channel', 'messages'));
    }

    public function communityJoin(Channel $channel)
    {
        $user = auth()->user();
        abort_unless($channel->canSelfJoin($user) || $channel->type === Channel::TYPE_PUBLIC, 403);

        if (! $channel->isMember($user)) {
            $channel->addMember($user, Channel::ROLE_MENTOR);
        }

        return redirect()
            ->route('mentor.community.show', $channel->slug)
            ->with('success', 'Joined channel.');
    }

    public function assessments()
    {
        $assessments = collect();

        try {
            if (Schema::hasTable('assessments')) {
                $assessments = Assessment::query()
                    ->orderBy('month')
                    ->get()
                    ->map(function (Assessment $a) {
                        $questions = $a->questions ?? [];
                        $a->question_count = is_array($questions) ? count($questions) : 0;
                        return $a;
                    });
            }
        } catch (\Throwable) {
            $assessments = collect();
        }

        $menteeCount = $this->mentorMenteesQuery()->count();

        return view('frontend.mentors.assessments', compact('assessments', 'menteeCount'));
    }

    private function mentorMenteesQuery()
    {
        $mentorId = auth()->id();

        $sessionIds = ConsultationSession::where('mentor_id', $mentorId)->pluck('mentee_id');
        $assignedIds = User::where('assigned_mentor_id', $mentorId)->where('role', 'mentee')->pluck('id');
        $enrolledIds = MenteeEnrollment::where('mentor_id', $mentorId)->pluck('mentee_id');

        $ids = $sessionIds->merge($assignedIds)->merge($enrolledIds)->unique()->filter()->values();

        return User::where('role', 'mentee')
            ->whereIn('id', $ids)
            ->orderBy('name');
    }

    private function findMentorMentee(int $menteeId): User
    {
        $exists = $this->mentorMenteesQuery()->where('id', $menteeId)->exists();
        abort_unless($exists, 404);

        return User::where('role', 'mentee')->findOrFail($menteeId);
    }
}
