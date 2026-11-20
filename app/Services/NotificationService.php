<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\CareerServiceRequest;
use App\Models\ConsultationSession;
use App\Models\MockInterviewRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificationService
{
    public function send(
        User|int|null $user,
        string $type,
        string $title,
        string $body,
        array $data = [],
        ?string $url = null
    ): ?Notification {
        $user = $this->resolveUser($user);
        if (! $user) {
            return null;
        }

        if (! Schema::hasTable('notifications')) {
            return null;
        }

        try {
            $notification = Notification::create([
                'user_id' => $user->id,
                'type'    => $type,
                'title'   => $title,
                'body'    => Notification::withoutAutoApproveCopy($body),
                'data'    => $data,
                'url'     => $url,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('In-app notification failed.', [
                'user_id' => $user->id,
                'type'    => $type,
                'error'   => $e->getMessage(),
            ]);

            return null;
        }

        $this->pushAfterResponse($user, $notification);

        return $notification;
    }

    public function sendToIds(iterable $userIds, string $type, string $title, string $body, array $data = [], ?string $url = null): void
    {
        foreach ($userIds as $id) {
            $this->send($id, $type, $title, $body, $data, $url);
        }
    }

    public function notifyAdmins(string $type, string $title, string $body, array $data = [], ?string $url = null): void
    {
        $this->sendToIds(
            User::query()->where('role', 'admin')->where('is_active', true)->pluck('id'),
            $type,
            $title,
            $body,
            $data,
            $url
        );
    }

    public function expertPendingApproval(User $expert): void
    {
        if (! $expert->isExpert()) {
            return;
        }

        $this->notifyAdmins(
            'expert_pending_approval',
            'Expert approval pending',
            "{$expert->name} submitted an expert profile and is waiting for approval.",
            ['expert_id' => $expert->id],
            route('admin.experts.edit', $expert)
        );
    }

    public function sendDueSessionReminders(): int
    {
        if (! Schema::hasTable('consultation_sessions')) {
            return 0;
        }

        $sessions = ConsultationSession::query()
            ->with(['mentor', 'mentee'])
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->whereBetween('scheduled_at', [now()->addMinutes(10), now()->addMinutes(20)])
            ->get();

        foreach ($sessions as $session) {
            $this->sessionEvent($session, 'reminder');
        }

        return $sessions->count();
    }

    /**
     * Career services (resume / LinkedIn) lifecycle for mentee + admin inbox.
     * Events: submitted, completed
     */
    public function careerServiceEvent(CareerServiceRequest $request, string $event): void
    {
        $request = $request->fresh(['user']) ?: $request;
        $mentee = $request->user;
        if (! $mentee) {
            return;
        }

        $label = $request->typeLabel();
        $typeKey = $request->type === CareerServiceRequest::TYPE_LINKEDIN ? 'linkedin' : 'resume';

        $copy = match ($event) {
            'submitted' => [
                'type'        => 'career_'.$typeKey.'_submitted',
                'menteeTitle' => $label.' request received',
                'menteeBody'  => "Your {$label} request (#{$request->id}) is under review. We’ll notify you when it’s ready.",
                'adminTitle'  => "New {$label} request",
                'adminBody'   => "{$mentee->name} submitted a {$label} request (#{$request->id}).",
            ],
            'completed' => [
                'type'        => 'career_'.$typeKey.'_completed',
                'menteeTitle' => $label.' is ready',
                'menteeBody'  => "Your {$label} deliverable is ready to download from Career Services.",
                'adminTitle'  => null,
                'adminBody'   => null,
            ],
            default => null,
        };

        if (! $copy) {
            return;
        }

        if (! $this->alreadySent((int) $mentee->id, $copy['type'], 'request_id', $request->id)) {
            $this->send(
                $mentee,
                $copy['type'],
                $copy['menteeTitle'],
                $copy['menteeBody'],
                [
                    'request_id' => $request->id,
                    'type'       => $request->type,
                    'event'      => $event,
                ],
                $this->roleUrl($mentee, 'career-services.show', $request)
            );
        }

        if ($event === 'submitted' && $copy['adminTitle']) {
            $adminType = 'admin_'.$copy['type'];
            if (! $this->alreadySentForAdmins($adminType, 'request_id', $request->id)) {
                $this->notifyAdmins(
                    $adminType,
                    $copy['adminTitle'],
                    $copy['adminBody'],
                    [
                        'request_id' => $request->id,
                        'type'       => $request->type,
                        'event'      => $event,
                        'user_id'    => $mentee->id,
                    ],
                    route('admin.career-services.show', $request)
                );
            }
        }
    }

    /**
     * Mock interview lifecycle for mentee + admin inbox.
     * Events: submitted, confirmed, completed, cancelled, notes_shared
     */
    public function mockInterviewEvent(MockInterviewRequest $request, string $event): void
    {
        $request = $request->fresh(['user']) ?: $request;
        $mentee = $request->user;
        if (! $mentee) {
            return;
        }

        $when = $request->preferred_at
            ? $request->preferred_at->timezone($request->timezone ?: 'Asia/Kolkata')->format('d M Y, g:i A')
            : 'your preferred slot';
        $mins = (int) $request->duration_minutes;

        $copy = match ($event) {
            'submitted' => [
                'type'        => 'mock_interview_submitted',
                'menteeTitle' => 'Mock interview request received',
                'menteeBody'  => "Your {$mins}-min mock interview request (#{$request->id}) for {$when} is awaiting confirmation.",
                'adminTitle'  => 'New mock interview request',
                'adminBody'   => "{$mentee->name} requested a {$mins}-min mock interview (#{$request->id}) for {$when}.",
            ],
            'confirmed' => [
                'type'        => 'mock_interview_confirmed',
                'menteeTitle' => 'Mock interview confirmed',
                'menteeBody'  => "Your mock interview (#{$request->id}) is confirmed for {$when}. Join from Mock Interviews when ready.",
                'adminTitle'  => null,
                'adminBody'   => null,
            ],
            'completed' => [
                'type'        => 'mock_interview_completed',
                'menteeTitle' => 'Mock interview completed',
                'menteeBody'  => "Your mock interview (#{$request->id}) is complete. Feedback and notes are available on the booking page.",
                'adminTitle'  => null,
                'adminBody'   => null,
            ],
            'cancelled' => [
                'type'        => 'mock_interview_cancelled',
                'menteeTitle' => 'Mock interview cancelled',
                'menteeBody'  => "Your mock interview (#{$request->id}) scheduled for {$when} was cancelled.",
                'adminTitle'  => null,
                'adminBody'   => null,
            ],
            'notes_shared' => [
                'type'        => 'mock_interview_notes_shared',
                'menteeTitle' => 'New interviewer notes',
                'menteeBody'  => "Your interviewer shared notes on mock interview #{$request->id}.",
                'adminTitle'  => null,
                'adminBody'   => null,
            ],
            default => null,
        };

        if (! $copy) {
            return;
        }

        if (! $this->alreadySent((int) $mentee->id, $copy['type'], 'request_id', $request->id)) {
            $this->send(
                $mentee,
                $copy['type'],
                $copy['menteeTitle'],
                $copy['menteeBody'],
                [
                    'request_id' => $request->id,
                    'event'      => $event,
                ],
                $this->roleUrl($mentee, 'mock-interviews.show', $request)
            );
        }

        if ($event === 'submitted' && $copy['adminTitle']) {
            $adminType = 'admin_'.$copy['type'];
            if (! $this->alreadySentForAdmins($adminType, 'request_id', $request->id)) {
                $this->notifyAdmins(
                    $adminType,
                    $copy['adminTitle'],
                    $copy['adminBody'],
                    [
                        'request_id' => $request->id,
                        'event'      => $event,
                        'user_id'    => $mentee->id,
                    ],
                    route('admin.mock-interviews.show', $request)
                );
            }
        }
    }

    public function alreadySentForAdmins(string $type, string $dataKey, mixed $dataValue): bool
    {
        if (! Schema::hasTable('notifications')) {
            return false;
        }

        return Notification::query()
            ->where('type', $type)
            ->where("data->{$dataKey}", $dataValue)
            ->whereHas('user', fn ($q) => $q->where('role', 'admin'))
            ->exists();
    }

    public function sessionEvent(ConsultationSession $session, string $event): void
    {
        $session = $session->fresh(['mentor', 'mentee']) ?: $session;

        if ($event === 'booked' && ! $this->settingOn('notify_booking', true)) {
            return;
        }

        if ($event === 'cancelled' && ! $this->settingOn('notify_cancellation', true)) {
            return;
        }

        $when = $session->scheduled_at
            ? $session->scheduled_at->timezone(config('app.timezone'))->format('d M Y, g:i A')
            : 'soon';

        $mentorName = $session->mentor?->name ?? 'your mentor';
        $menteeName = $session->mentee?->name ?? 'your mentee';
        $joinNow = $event === 'booked' && $session->canJoinCall();

        $copy = match ($event) {
            'booked' => [
                'type'        => 'session_booked',
                'menteeTitle' => $joinNow ? 'Session ready to join' : 'Session booked',
                'menteeBody'  => $joinNow
                    ? "Your session with {$mentorName} is ready. You can join now."
                    : "Your session with {$mentorName} is scheduled for {$when}.",
                'mentorTitle' => $joinNow ? 'Session ready to join' : 'New session booked',
                'mentorBody'  => $joinNow
                    ? "Your session with {$menteeName} is ready. You can join now."
                    : "{$menteeName} booked a session with you for {$when}.",
            ],
            'cancelled' => [
                'type'        => 'session_cancelled',
                'menteeTitle' => 'Session cancelled',
                'menteeBody'  => "Your session with {$mentorName} on {$when} was cancelled.",
                'mentorTitle' => 'Session cancelled',
                'mentorBody'  => "The session with {$menteeName} on {$when} was cancelled.",
            ],
            'completed' => [
                'type'        => 'session_completed',
                'menteeTitle' => 'Session completed',
                'menteeBody'  => "Your session with {$mentorName} is complete.",
                'mentorTitle' => 'Session completed',
                'mentorBody'  => "Your session with {$menteeName} is complete.",
            ],
            'reminder' => [
                'type'        => 'session_reminder',
                'menteeTitle' => 'Session starting soon',
                'menteeBody'  => "Your session with {$mentorName} starts at {$when}.",
                'mentorTitle' => 'Session starting soon',
                'mentorBody'  => "Your session with {$menteeName} starts at {$when}.",
            ],
            default => null,
        };

        if (! $copy) {
            return;
        }

        if ($session->mentee) {
            $this->sendSessionCopy($session, $session->mentee, $event, $copy['type'], $copy['menteeTitle'], $copy['menteeBody']);
        }

        if ($session->mentor) {
            $this->sendSessionCopy($session, $session->mentor, $event, $copy['type'], $copy['mentorTitle'], $copy['mentorBody']);
        }
    }

    private function sendSessionCopy(
        ConsultationSession $session,
        User $recipient,
        string $event,
        string $type,
        string $title,
        string $body
    ): void {
        if ($event === 'reminder' && $this->alreadySent((int) $recipient->id, $type, 'session_id', $session->id)) {
            return;
        }

        $this->send(
            $recipient,
            $type,
            $title,
            $body,
            ['session_id' => $session->id, 'event' => $event],
            $this->roleUrl($recipient, 'sessions.show', $session->id)
        );
    }

    public function alreadySent(int $userId, string $type, string $dataKey, mixed $dataValue): bool
    {
        if (! Schema::hasTable('notifications')) {
            return false;
        }

        return Notification::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where("data->{$dataKey}", $dataValue)
            ->exists();
    }

    public function roleUrl(User $user, string $suffix, mixed $params = []): ?string
    {
        $prefix = $user->isSessionHost() ? 'mentor.' : ($user->isMentee() ? 'mentee.' : null);
        if (! $prefix) {
            return null;
        }

        $aliases = [
            'community.index' => $user->isMentor() ? 'community' : 'community.index',
            'plans'           => $user->isMentee() ? 'plans' : 'wallet',
        ];
        $suffix = $aliases[$suffix] ?? $suffix;

        try {
            return route($prefix.$suffix, $params);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveUser(User|int|null $user): ?User
    {
        if ($user instanceof User) {
            return $user;
        }

        if (! $user) {
            return null;
        }

        return User::query()->find($user);
    }

    private function settingOn(string $key, bool $default = true): bool
    {
        $value = AppSetting::get($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }

    private function pushAfterResponse(User $user, Notification $notification): void
    {
        $userId = $user->id;
        $notificationId = $notification->id;

        dispatch(function () use ($userId, $notificationId) {
            $freshUser = User::query()->find($userId);
            $freshNotification = Notification::query()->find($notificationId);
            if (! $freshUser || ! $freshNotification) {
                return;
            }

            try {
                app(FcmPushService::class)->sendToUser($freshUser, $freshNotification);
            } catch (\Throwable $e) {
                Log::warning('Device push failed.', [
                    'user_id'         => $userId,
                    'notification_id' => $notificationId,
                    'error'           => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }
}
