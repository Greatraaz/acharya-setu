<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    public function sendToUser(User $user, Notification $notification): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->filter()->unique()->values();
        if ($tokens->isEmpty()) {
            return;
        }

        $credentials = AppSetting::fcmServiceAccount();
        if ($credentials !== null) {
            $this->dispatchV1($credentials, $tokens->all(), $notification);

            return;
        }

        $serverKey = AppSetting::fcmServerKey();
        if ($serverKey === '') {
            return;
        }

        foreach ($tokens->chunk(100) as $chunk) {
            $this->dispatchLegacy($serverKey, $chunk->all(), $notification);
        }
    }

    /**
     * @param  array<string, mixed>  $credentials
     * @param  list<string>  $tokens
     */
    private function dispatchV1(array $credentials, array $tokens, Notification $notification): void
    {
        $projectId = (string) ($credentials['project_id'] ?? '');
        $accessToken = $this->googleAccessToken($credentials);
        if ($projectId === '' || $accessToken === '') {
            return;
        }

        $data = [
            'id'           => (string) $notification->id,
            'type'         => (string) $notification->type,
            'url'          => (string) ($notification->url ?? ''),
            'title'        => (string) $notification->title,
            'body'         => (string) $notification->body,
            'click_action' => (string) ($notification->url ?? ''),
        ];

        $invalid = [];

        foreach ($tokens as $token) {
            try {
                $response = Http::timeout(12)
                    ->withToken($accessToken)
                    ->post('https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send', [
                        'message' => [
                            'token'        => $token,
                            'notification' => [
                                'title' => $notification->title,
                                'body'  => (string) $notification->body,
                            ],
                            'data'    => $data,
                            'android' => ['priority' => 'HIGH'],
                            'apns'    => [
                                'headers' => ['apns-priority' => '10'],
                                'payload' => ['aps' => ['sound' => 'default']],
                            ],
                        ],
                    ]);
            } catch (\Throwable $e) {
                Log::warning('FCM v1 push request failed.', [
                    'notification_id' => $notification->id,
                    'error'           => $e->getMessage(),
                ]);

                continue;
            }

            if ($response->successful()) {
                continue;
            }

            $status = $response->status();
            $errorCode = (string) data_get($response->json(), 'error.status', '');
            $errorMessage = strtolower((string) data_get($response->json(), 'error.message', ''));

            if (
                $status === 404
                || in_array($errorCode, ['NOT_FOUND', 'INVALID_ARGUMENT'], true)
                || str_contains($errorMessage, 'unregistered')
                || str_contains($errorMessage, 'not a valid fcm registration token')
            ) {
                $invalid[] = $token;
            } else {
                Log::warning('FCM v1 push rejected.', [
                    'notification_id' => $notification->id,
                    'status'          => $status,
                    'error'           => $errorCode,
                ]);
            }
        }

        if ($invalid !== []) {
            DeviceToken::whereIn('token', $invalid)->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function googleAccessToken(array $credentials): string
    {
        $email = (string) ($credentials['client_email'] ?? '');
        $privateKey = (string) ($credentials['private_key'] ?? '');
        if ($email === '' || $privateKey === '') {
            return '';
        }

        $cacheKey = 'fcm_v1_token_'.md5($email);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $now = time();
        $unsigned = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES))
            .'.'
            .$this->base64Url(json_encode([
                'iss'   => $email,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], JSON_UNESCAPED_SLASHES));

        $signature = '';
        $ok = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (! $ok || $signature === '') {
            Log::warning('FCM v1 JWT signing failed.');

            return '';
        }

        try {
            $response = Http::timeout(12)
                ->asForm()
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $unsigned.'.'.$this->base64Url($signature),
                ]);
        } catch (\Throwable $e) {
            Log::warning('FCM v1 token request failed.', ['error' => $e->getMessage()]);

            return '';
        }

        $accessToken = (string) $response->json('access_token', '');
        if ($accessToken === '') {
            Log::warning('FCM v1 token rejected.', ['status' => $response->status()]);

            return '';
        }

        $ttl = max(60, ((int) $response->json('expires_in', 3600)) - 60);
        Cache::put($cacheKey, $accessToken, $ttl);

        return $accessToken;
    }

    /**
     * @param  list<string>  $tokens
     */
    private function dispatchLegacy(string $serverKey, array $tokens, Notification $notification): void
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
                'id'           => (string) $notification->id,
                'type'         => (string) $notification->type,
                'url'          => (string) ($notification->url ?? ''),
                'title'        => (string) $notification->title,
                'body'         => (string) $notification->body,
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

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
