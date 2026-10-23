<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ConsultationSession;
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
                'body'    => $body,
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

        $copy = match ($event) {
            'booked' => [
                'type'        => 'session_booked',
                'menteeTitle' => 'Session booked',
                'menteeBody'  => "Your session with {$mentorName} is scheduled for {$when}.",
                'mentorTitle' => 'New session booked',
                'mentorBody'  => "{$menteeName} booked a session with you for {$when}.",
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
        $prefix = $user->isMentor() ? 'mentor.' : ($user->isMentee() ? 'mentee.' : null);
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
