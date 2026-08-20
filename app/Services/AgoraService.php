<?php

namespace App\Services;

use App\Helpers\Agora\RtcTokenBuilder;
use App\Models\AppSetting;
use App\Models\ConsultationSession;
use App\Models\User;
use App\Models\VideoCallLog;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AgoraService
{
    public function credentials(): array
    {
        $settings = AppSetting::agora();

        $appId = trim((string) ($settings['app_id'] ?: config('services.agora.app_id', '')));
        $appCert = trim((string) ($settings['app_cert'] ?: config('services.agora.app_certificate', '')));
        $expiry = (int) ($settings['token_expiry'] ?: config('services.agora.token_expiry', 7200));

        return [
            'app_id'       => $appId,
            'app_cert'     => $appCert,
            'token_expiry' => max(600, $expiry),    
        ];
    }

    public function isConfigured(): bool
    {
        $creds = $this->credentials();

        return $creds['app_id'] !== '' && $creds['app_cert'] !== '';
    }

    public function issueToken(User $user, ConsultationSession $session): array
    {
        $this->assertParticipant($user, $session);
        $this->assertJoinable($session);

        if (! $this->isConfigured()) {
            throw new HttpException(503, 'Video calling is not configured. Add Agora App ID and Certificate in Admin → App Settings.');
        }

        if (blank($session->meeting_channel)) {
            $session->update([
                'meeting_channel'  => strtoupper(Str::random(10)),
                'meeting_provider' => 'agora',
            ]);
            $session->refresh();
        } elseif (blank($session->meeting_provider)) {
            $session->update(['meeting_provider' => 'agora']);
        }

        $creds = $this->credentials();
        //$uid = (int) $user->id;
        $uid = 0;
        $expireTs = time() + $creds['token_expiry'];

        $token = RtcTokenBuilder::buildTokenWithUid(
            $creds['app_id'],
            $creds['app_cert'],
            $session->meeting_channel,
            $uid,
            RtcTokenBuilder::RolePublisher,
            $expireTs
        );

        if (! $session->started_at && in_array($session->status, [
            ConsultationSession::STATUS_PENDING,
            ConsultationSession::STATUS_CONFIRMED,
            ConsultationSession::STATUS_UPCOMING,
            ConsultationSession::STATUS_ONGOING,
        ], true)) {
            $session->start();
        }

        $log = $this->startCallLog($session, $user);

        return [
            'app_id'       => $creds['app_id'],
            'channel'      => $session->meeting_channel,
            'token'        => $token,
            'uid'          => $uid,
            'role'         => (int) $session->mentor_id === $uid ? 'mentor' : 'mentee',
            'expires_at'   => $expireTs,
            'call_log_id'  => $log->id,
            'peer'         => $this->peerPayload($session, $user),
            'session'      => [
                'id'            => $session->id,
                'title'         => $session->title ?: 'Mentoring Session',
                'scheduled_at'  => $session->scheduled_at?->toIso8601String(),
                'duration'      => (int) ($session->duration_minutes ?? 30),
                'status'        => $session->status,
            ],
        ];
    }

    public function endCall(User $user, ConsultationSession $session, string $reason = 'normal'): void
    {
        $this->assertParticipant($user, $session);

        $log = VideoCallLog::query()
            ->where('booking_id', $session->id)
            ->whereIn('status', [VideoCallLog::STATUS_INITIATED, VideoCallLog::STATUS_ONGOING])
            ->latest()
            ->first();

        if (! $log) {
            return;
        }

        $participant = $log->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->latest()
            ->first();

        $participant?->markLeft();

        $stillIn = $log->participants()->whereNull('left_at')->exists();
        $shouldEndLog = ! $stillIn || (int) $session->mentor_id === (int) $user->id;

        if ($shouldEndLog) {
            $log->markEnded($reason);
            $this->maybeCompleteSession($session->fresh(), $log->fresh());
        }
    }

    private function maybeCompleteSession(ConsultationSession $session, VideoCallLog $log): void
    {
        if ($session->status !== ConsultationSession::STATUS_ONGOING) {
            return;
        }

        if ($log->duration_seconds) {
            $session->update([
                'actual_duration_seconds' => $log->duration_seconds,
            ]);
        }

        $session->complete();
    }

    public function assertParticipant(User $user, ConsultationSession $session): void
    {
        if ((int) $session->mentor_id !== (int) $user->id && (int) $session->mentee_id !== (int) $user->id) {
            throw new HttpException(403, 'You are not part of this session.');
        }
    }

    public function assertJoinable(ConsultationSession $session): void
    {
        if (in_array($session->status, [
            ConsultationSession::STATUS_COMPLETED,
            ConsultationSession::STATUS_CANCELLED,
            ConsultationSession::STATUS_NO_SHOW,
        ], true)) {
            throw new HttpException(403, 'This session is no longer available to join.');
        }

        if (! in_array($session->status, [
            ConsultationSession::STATUS_PENDING,
            ConsultationSession::STATUS_CONFIRMED,
            ConsultationSession::STATUS_UPCOMING,
            ConsultationSession::STATUS_ONGOING,
        ], true)) {
            throw new HttpException(403, 'This session cannot be joined right now.');
        }
    }

    private function startCallLog(ConsultationSession $session, User $user): VideoCallLog
    {
        $log = VideoCallLog::query()
            ->where('booking_id', $session->id)
            ->whereIn('status', [VideoCallLog::STATUS_INITIATED, VideoCallLog::STATUS_ONGOING])
            ->latest()
            ->first();

        if (! $log) {
            $log = VideoCallLog::create([
                'host_id'        => $session->mentor_id,
                'participant_id' => $session->mentee_id,
                'channel_name'   => $session->meeting_channel,
                'session_id'     => (string) $session->id,
                'provider'       => VideoCallLog::PROVIDER_AGORA,
                'call_type'      => 'video',
                'booking_id'     => $session->id,
                'status'         => VideoCallLog::STATUS_ONGOING,
                'started_at'     => now(),
            ]);
        } else {
            $log->markStarted();
        }

        $alreadyIn = $log->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();

        if (! $alreadyIn) {
            $log->participants()->create([
                'user_id'      => $user->id,
                'display_name' => $user->name,
                'role'         => (int) $user->id === (int) $session->mentor_id ? 'host' : 'participant',
                'joined_at'    => now(),
            ]);
        }

        return $log;
    }

    private function peerPayload(ConsultationSession $session, User $user): array
    {
        $peer = (int) $session->mentor_id === (int) $user->id
            ? $session->mentee
            : $session->mentor;

        return [
            'id'         => $peer?->id,
            'name'       => $peer?->name ?? 'Participant',
            'avatar_url' => $peer?->avatar_url,
        ];
    }
}
