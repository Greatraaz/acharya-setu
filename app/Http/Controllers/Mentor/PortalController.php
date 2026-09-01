<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Channel;
use App\Models\ConsultationSession;
use App\Models\EducationStream;
use App\Models\MenteeEnrollment;
use App\Models\SessionNote;
use App\Models\StudentCurriculumProgress;
use App\Models\User;
use App\Support\ChannelIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PortalController extends Controller
{
    public function notes(Request $request)
    {
        $mentorId = auth()->id();

        $search = trim((string) $request->input('search', $request->input('q', '')));
        $visibility = $request->input('visibility');

        $notes = SessionNote::query()
            ->whereHas('session', fn ($q) => $q->where('mentor_id', $mentorId))
            ->with(['session.mentee', 'author'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('content', 'like', '%'.$search.'%')
                        ->orWhereHas('session', function ($s) use ($search) {
                            $s->where('title', 'like', '%'.$search.'%')
                                ->orWhereHas('mentee', fn ($m) => $m->where('name', 'like', '%'.$search.'%'));
                        });
                });
            })
            ->when($visibility === 'shared', fn ($q) => $q->where('is_shared', true))
            ->when($visibility === 'private', fn ($q) => $q->where('is_shared', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $sessionsWithoutNotes = ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', 'completed')
            ->whereDoesntHave('notes')
            ->with('mentee')
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        return view('frontend.mentors.notes', compact('notes', 'sessionsWithoutNotes', 'search', 'visibility'));
    }

    public function mentees(Request $request)
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $mentees = $this->mentorMenteesQuery()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('college', 'like', '%'.$search.'%')
                        ->orWhere('field', 'like', '%'.$search.'%');
                });
            })
            ->paginate(20)
            ->withQueryString();

        return view('frontend.mentors.mentees', compact('mentees', 'search'));
    }

    public function menteeShow(int $id)
    {
        $mentee = $this->findMentorMentee($id);
        $mentorId = auth()->id();

        $this->syncEnrollmentsFromTracks($mentorId, $mentee->id);

        $sessions = ConsultationSession::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->latest('scheduled_at')
            ->limit(15)
            ->get();

        $enrollments = MenteeEnrollment::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->with('stream')
            ->get();

        $tracks = EducationStream::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->withCount('months')
            ->orderBy('sort_order')
            ->get();

        return view('frontend.mentors.mentee-show', compact('mentee', 'sessions', 'enrollments', 'tracks'));
    }

    public function journey(Request $request)
    {
        $mentorId = auth()->id();

        $this->syncEnrollmentsFromTracks($mentorId);

        $search = trim((string) $request->input('search', $request->input('q', '')));
        $status = $request->input('status');

        $enrollments = MenteeEnrollment::where('mentor_id', $mentorId)
            ->with(['mentee', 'stream'])
            ->when($search !== '', fn ($q) => $q->whereHas('mentee', fn ($m) => $m->where('name', 'like', '%'.$search.'%')))
            ->when(in_array($status, ['active', 'completed', 'paused'], true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function (MenteeEnrollment $enrollment) {
                $enrollment->progress_data = $enrollment->progress;

                return $enrollment;
            });

        $assignedMenteeIds = EducationStream::where('mentor_id', $mentorId)
            ->whereNotNull('mentee_id')
            ->pluck('mentee_id')
            ->merge(
                MenteeEnrollment::where('mentor_id', $mentorId)->pluck('mentee_id')
            )
            ->unique()
            ->filter()
            ->values();

        $menteesWithoutEnrollment = $this->mentorMenteesQuery()
            ->whereNotIn('id', $assignedMenteeIds)
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->limit(10)
            ->get();

        return view('frontend.mentors.journey', compact(
            'enrollments',
            'menteesWithoutEnrollment',
            'search',
            'status'
        ));
    }

    public function journeyShow(int $menteeId)
    {
        $mentee = $this->findMentorMentee($menteeId);
        $mentorId = auth()->id();

        $this->syncEnrollmentsFromTracks($mentorId, $mentee->id);

        $enrollments = MenteeEnrollment::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->with('stream')
            ->get()
            ->map(function (MenteeEnrollment $enrollment) {
                $enrollment->progress_data = $enrollment->progress;
                return $enrollment;
            });

        // Fallback: show tracks even if enrollment sync somehow missed them.
        $tracks = EducationStream::where('mentor_id', $mentorId)
            ->where('mentee_id', $mentee->id)
            ->orderBy('sort_order')
            ->get()
            ->map(function (EducationStream $track) use ($mentee) {
                $track->progress_data = StudentCurriculumProgress::getOverallProgress($mentee->id, $track->id);
                return $track;
            });

        return view('frontend.mentors.journey-show', compact('mentee', 'enrollments', 'tracks'));
    }

    public function community(Request $request)
    {
        $user = auth()->user();
        $channels = ChannelIndexQuery::paginate($user, $request, 18);

        return view('frontend.mentors.community', compact('channels'));
    }

    public function communityShow(Channel $channel)
    {
        $user = auth()->user();
        abort_unless($channel->canAccess($user), 403);

        if ($channel->isMember($user)) {
            $channel->markRead($user);
        }

        $messages = $channel->paginateMessagesForUser($user, 30);

        $channels = Channel::visibleTo($user)
            ->withCount(['allMessages', 'members'])
            ->latest()
            ->get()
            ->map(function (Channel $ch) use ($user) {
                $ch->unread_count = $ch->isMember($user) ? $ch->unreadCountFor($user) : 0;
                return $ch;
            });

        $members = $channel->members()
            ->select('users.id', 'users.name', 'users.avatar_url', 'users.role')
            ->orderByPivot('role')
            ->get();

        $inviteCandidates = User::query()
            ->whereIn('role', ['mentor', 'mentee'])
            ->where('is_active', true)
            ->whereNotIn('id', $members->pluck('id'))
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'role']);

        $isMember   = $channel->isMember($user);
        $isAdmin    = $channel->isAdmin($user)
            || (int) $channel->created_by === (int) $user->id
            || $user->isAdmin();
        $isCreator  = (int) $channel->created_by === (int) $user->id;

        return view('frontend.mentors.community-show', compact(
            'channel', 'messages', 'channels', 'members',
            'inviteCandidates', 'isMember', 'isAdmin', 'isCreator'
        ));
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
                    ->withCount('questions')
                    ->latest()
                    ->get()
                    ->map(function (Assessment $a) {
                        $a->question_count = (int) $a->questions_count;
                        return $a;
                    });
            }
        } catch (\Throwable) {
            $assessments = collect();
        }

        $menteeCount = $this->mentorMenteesQuery()->count();

        return view('frontend.mentors.assessments', compact('assessments', 'menteeCount'));
    }

    private function syncEnrollmentsFromTracks(int $mentorId, ?int $menteeId = null): void
    {
        $tracks = EducationStream::where('mentor_id', $mentorId)
            ->whereNotNull('mentee_id')
            ->when($menteeId, fn ($q) => $q->where('mentee_id', $menteeId))
            ->get(['id', 'mentee_id', 'mentor_id']);

        foreach ($tracks as $track) {
            MenteeEnrollment::firstOrCreate(
                [
                    'mentee_id' => $track->mentee_id,
                    'mentor_id' => $track->mentor_id,
                    'stream_id' => $track->id,
                ],
                [
                    'start_date'        => now()->toDateString(),
                    'expected_end_date' => now()->addMonths(6)->toDateString(),
                    'status'            => 'active',
                    'current_month'     => 1,
                    'current_week'      => 1,
                ]
            );
        }
    }

    private function mentorMenteesQuery()
    {
        $mentorId = auth()->id();

        $sessionIds = ConsultationSession::where('mentor_id', $mentorId)->pluck('mentee_id');
        $assignedIds = User::where('assigned_mentor_id', $mentorId)->where('role', 'mentee')->pluck('id');
        $enrolledIds = MenteeEnrollment::where('mentor_id', $mentorId)->pluck('mentee_id');
        $trackIds = EducationStream::where('mentor_id', $mentorId)->pluck('mentee_id');

        $ids = $sessionIds->merge($assignedIds)->merge($enrolledIds)->merge($trackIds)->unique()->filter()->values();

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
