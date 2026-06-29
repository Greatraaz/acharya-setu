<?php

namespace App\Services;
 
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
 
class ActivityLogger
{
    private array $payload = [];
 
    public function __construct()
    {
        $this->payload = [
            'logged_at'  => now(),
            'level'      => ActivityLog::LEVEL_INFO,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 255),
            'url'        => substr(Request::fullUrl(), 0, 500),
            'method'     => Request::method(),
        ];
 
        // Auto-fill causer from Auth
        if (Auth::check()) {
            $user = Auth::user();
            $this->payload['causer_id']   = $user->id;
            $this->payload['causer_type'] = get_class($user);
            $this->payload['causer_name'] = $user->name;
        }
    }
 
    // ── Fluent builder ─────────────────────────────────────────
    public function by(Model $causer): static
    {
        $this->payload['causer_id']   = $causer->getKey();
        $this->payload['causer_type'] = get_class($causer);
        $this->payload['causer_name'] = $causer->name ?? (string) $causer->getKey();
        return $this;
    }
 
    public function on(Model $subject, ?string $label = null): static
    {
        $this->payload['subject_id']    = $subject->getKey();
        $this->payload['subject_type']  = get_class($subject);
        $this->payload['subject_label'] = $label ?? ($subject->name ?? $subject->title ?? (string) $subject->getKey());
        return $this;
    }
 
    public function event(string $event): static
    {
        $this->payload['event'] = $event;
        return $this;
    }
 
    public function description(string $desc): static
    {
        $this->payload['description'] = $desc;
        return $this;
    }
 
    public function module(string $module): static
    {
        $this->payload['module'] = $module;
        return $this;
    }
 
    public function level(string $level): static
    {
        $this->payload['level'] = $level;
        return $this;
    }
 
    public function info(): static    { return $this->level(ActivityLog::LEVEL_INFO); }
    public function success(): static { return $this->level(ActivityLog::LEVEL_SUCCESS); }
    public function warning(): static { return $this->level(ActivityLog::LEVEL_WARNING); }
    public function danger(): static  { return $this->level(ActivityLog::LEVEL_DANGER); }
 
    public function withOld(array $old): static
    {
        $this->payload['properties'] = array_merge($this->payload['properties'] ?? [], ['old' => $old]);
        return $this;
    }
 
    public function withNew(array $new): static
    {
        $this->payload['properties'] = array_merge($this->payload['properties'] ?? [], ['new' => $new]);
        return $this;
    }
 
    public function withMeta(array $meta): static
    {
        $this->payload['properties'] = array_merge($this->payload['properties'] ?? [], ['meta' => $meta]);
        return $this;
    }
 
    public function withChanges(Model $model): static
    {
        $dirty = $model->getDirty();
        $old   = array_intersect_key($model->getOriginal(), $dirty);
        return $this->withOld($old)->withNew($dirty);
    }
 
    public function log(): ActivityLog
    {
        return ActivityLog::create($this->payload);
    }
 
    // ── Static shortcuts ───────────────────────────────────────
    public static function record(string $event, string $description, string $module = 'system', string $level = 'info'): ActivityLog
    {
        return (new static())
            ->event($event)
            ->description($description)
            ->module($module)
            ->level($level)
            ->log();
    }
 
    // Auth events
    public static function login(\App\Models\User $user): ActivityLog
    {
        return (new static())
            ->by($user)
            ->event('login')
            ->description("{$user->name} logged in")
            ->module('auth')
            ->success()
            ->log();
    }
 
    public static function logout(\App\Models\User $user): ActivityLog
    {
        return (new static())
            ->by($user)
            ->event('logout')
            ->description("{$user->name} logged out")
            ->module('auth')
            ->info()
            ->log();
    }
 
    public static function loginFailed(string $email): ActivityLog
    {
        return (new static())
            ->event('login_failed')
            ->description("Failed login attempt for: {$email}")
            ->module('auth')
            ->withMeta(['attempted_email' => $email])
            ->warning()
            ->log();
    }
 
    // Model CRUD shortcuts
    public static function created(Model $model, string $module, string $label = ''): ActivityLog
    {
        $name = $label ?: (class_basename($model));
        return (new static())
            ->on($model)
            ->event('created')
            ->description(auth()->user()?->name . " created {$name} #{$model->getKey()}")
            ->module($module)
            ->success()
            ->log();
    }
 
    public static function updated(Model $model, string $module, string $label = ''): ActivityLog
    {
        $name = $label ?: class_basename($model);
        return (new static())
            ->on($model)
            ->event('updated')
            ->description(auth()->user()?->name . " updated {$name} #{$model->getKey()}")
            ->module($module)
            ->withChanges($model)
            ->info()
            ->log();
    }
 
    public static function deleted(Model $model, string $module, string $label = ''): ActivityLog
    {
        $name = $label ?: class_basename($model);
        return (new static())
            ->on($model)
            ->event('deleted')
            ->description(auth()->user()?->name . " deleted {$name} #{$model->getKey()}")
            ->module($module)
            ->danger()
            ->log();
    }
}