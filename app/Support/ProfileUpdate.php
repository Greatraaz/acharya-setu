<?php

namespace App\Support;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class ProfileUpdate
{
    /** Public mentor fields that stay unchanged until an admin approves them. */
    public const MODERATED = [
        'bio',
        'designation',
        'company',
        'experience_years',
        'rate_per_minute',
        'linkedin',
        'expertise',
        'strengths',
        'field',
    ];

    /**
     * @param  array<string, mixed>  $data
     * @return array{pending: bool, message: string}
     */
    public static function save(User $user, array $data): array
    {
        if (array_key_exists('bio', $data) && is_string($data['bio'])) {
            $data['bio'] = RichText::sanitize($data['bio']);
        }

        if (array_key_exists('phone', $data)) {
            $data['phone'] = IndianPhone::normalize($data['phone']);
            self::assertPhoneAvailable($user, $data['phone']);
        }

        $immediate = [];
        foreach (['name', 'phone', 'gender'] as $key) {
            if (array_key_exists($key, $data)) {
                $immediate[$key] = $data[$key];
            }
        }

        if ($user->role === 'mentee') {
            foreach (['education_stream', 'college', 'year', 'field', 'career_goals', 'location'] as $key) {
                if (array_key_exists($key, $data)) {
                    $immediate[$key] = $data[$key];
                }
            }
        }

        if (array_key_exists('preferences', $data) && is_array($data['preferences'])) {
            $immediate['preferences'] = self::mergePreferences($user->preferences ?? [], $data['preferences']);
        }

        $pending = false;
        if ($user->role === 'mentor' && $user->isApproved()) {
            $pending = self::queueMentorChanges($user, $data);
        } elseif ($user->role === 'mentor') {
            foreach (self::MODERATED as $key) {
                if (array_key_exists($key, $data)) {
                    $immediate[$key] = $data[$key];
                }
            }
        }

        if ($immediate !== []) {
            $user->update($immediate);
        }

        if ($pending) {
            return [
                'pending' => true,
                'message' => 'Changes submitted for review. Your public profile stays the same until an admin approves them.',
            ];
        }

        return [
            'pending' => false,
            'message' => 'Profile updated.',
        ];
    }

    public static function storeAvatar(User $user, UploadedFile $file): string
    {
        self::deleteAvatarFile($user->avatar_url);
        $path = $file->store('avatars', 'public');
        $url = '/storage/'.$path;
        $user->update(['avatar_url' => $url]);

        return $url;
    }

    public static function storeAvatarContents(User $user, string $binary, string $extension): string
    {
        self::deleteAvatarFile($user->avatar_url);
        $fileName = 'avatar_'.$user->id.'_'.time().'.'.$extension;
        $uploadPath = public_path('upload/avatar');
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        file_put_contents($uploadPath.'/'.$fileName, $binary);
        $url = url('upload/avatar/'.$fileName);
        $user->update(['avatar_url' => $url]);

        return $url;
    }

    public static function clearAvatar(User $user): void
    {
        self::deleteAvatarFile($user->avatar_url);
        $user->update(['avatar_url' => null]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function queueMentorChanges(User $user, array $data): bool
    {
        $pending = $user->pendingChanges()->pending()->first();
        $changes = $pending?->changes ?? [];

        foreach (self::MODERATED as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            if (self::same($user, $key, $data[$key])) {
                unset($changes[$key]);
            } else {
                $changes[$key] = $data[$key];
            }
        }

        $changes = array_intersect_key($changes, array_flip(self::MODERATED));

        if ($changes === []) {
            $user->pendingChanges()->pending()->delete();
            if ($user->has_pending_changes) {
                $user->update(['has_pending_changes' => false]);
            }

            return false;
        }

        $user->requestProfileChange($changes);
        ActivityLogger::record(
            'profile_change_requested',
            $user->name.' submitted profile changes for review',
            'users',
            'info'
        );

        return true;
    }

    private static function same(User $user, string $key, mixed $value): bool
    {
        $current = $user->{$key};
        if ($key === 'rate_per_minute') {
            return round((float) $current, 2) === round((float) $value, 2);
        }
        if (in_array($key, ['expertise', 'strengths'], true)) {
            return json_encode(array_values((array) $current)) === json_encode(array_values((array) $value));
        }

        return trim((string) $current) === trim((string) $value);
    }

    private static function assertPhoneAvailable(User $user, ?string $phone): void
    {
        if ($phone === null || $phone === '') {
            return;
        }

        $owner = User::findByPhone($phone);
        if ($owner && $owner->id !== $user->id) {
            throw ValidationException::withMessages([
                'phone' => 'This mobile number is already registered.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    private static function mergePreferences(array $current, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if ($key === 'notifications' && is_array($value)) {
                $current['notifications'] = array_merge($current['notifications'] ?? [], $value);
                continue;
            }
            $current[$key] = $value;
        }

        return $current;
    }

    private static function deleteAvatarFile(?string $url): void
    {
        if ($url === null || $url === '') {
            return;
        }

        if (str_starts_with($url, '/storage/')) {
            Storage::disk('public')->delete(substr($url, strlen('/storage/')));
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (str_contains($path, '/upload/avatar/')) {
            $file = public_path('upload/avatar/'.basename($path));
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
