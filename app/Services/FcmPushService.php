<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    public function sendToUser(User $user, Notification $notification): void
    {
        $serverKey = AppSetting::fcmServerKey();
        if ($serverKey === '') {
            return;
        }

        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->filter()->unique()->values();
        if ($tokens->isEmpty()) {
            return;
        }

        foreach ($tokens->chunk(100) as $chunk) {
            $this->dispatch($serverKey, $chunk->all(), $notification);
        }
    }

    /**
     * @param  list<string>  $tokens
     */
    private function dispatch(string $serverKey, array $tokens, Notification $notification): void
    {
        $payload = [
            'registration_ids' => array_values($tokens),
            'priority'         => 'high',
            'notification'     => [
                'title' => $notification->title,
                'body'  => (string) $notification->body,
                'sound' => 'default',
            ],
            'data' => [
                'id'     => (string) $notification->id,
                'type'   => (string) $notification->type,
                'url'    => (string) ($notification->url ?? ''),
                'title'  => (string) $notification->title,
                'body'   => (string) $notification->body,
                'click_action' => (string) ($notification->url ?? ''),
            ],
        ];

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'Authorization' => 'key='.$serverKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://fcm.googleapis.com/fcm/send', $payload);
        } catch (\Throwable $e) {
            Log::warning('FCM push request failed.', [
                'notification_id' => $notification->id,
                'error'           => $e->getMessage(),
            ]);

            return;
        }

        if (! $response->successful()) {
            Log::warning('FCM push rejected.', [
                'notification_id' => $notification->id,
                'status'          => $response->status(),
            ]);

            return;
        }

        $results = $response->json('results') ?? [];
        $invalid = [];

        foreach ($results as $index => $result) {
            if (! is_array($result)) {
                continue;
            }

            $error = $result['error'] ?? null;
            if (in_array($error, ['NotRegistered', 'InvalidRegistration', 'MismatchSenderId'], true)) {
                $invalid[] = $tokens[$index] ?? null;
            }
        }

        $invalid = array_values(array_filter($invalid));
        if ($invalid !== []) {
            DeviceToken::whereIn('token', $invalid)->delete();
        }
    }
}
