<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_call_logs', function (Blueprint $table) {
            $table->id();

            // Participants
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('users')->nullOnDelete();

            // Session identity
            $table->string('channel_name')->index();         // Agora channel / Zoom meeting ID / Google meet code
            $table->string('session_id')->nullable()->index();// External session/meeting ID from provider
            $table->string('provider')->default('agora');    // agora | zoom | google
            $table->string('call_type')->default('video');   // video | audio | screen

            // Booking / appointment context (optional FK — set null if no booking system)
            $table->unsignedBigInteger('booking_id')->nullable()->index();

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable(); // computed on end

            // Status lifecycle
            // initiated → ongoing → completed | missed | failed | cancelled
            $table->string('status')->default('initiated');

            // Quality & metadata
            $table->string('end_reason')->nullable();        // normal | host_left | timeout | error | cancelled
            $table->unsignedTinyInteger('host_rating')->nullable();         // 1-5
            $table->unsignedTinyInteger('participant_rating')->nullable();  // 1-5
            $table->text('host_notes')->nullable();
            $table->json('meta')->nullable();                // provider-specific extras (recording URL, etc.)

            // Recording
            $table->boolean('is_recorded')->default(false);
            $table->string('recording_url')->nullable();
            $table->unsignedBigInteger('recording_size_kb')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Useful composite indexes
            $table->index(['host_id', 'started_at']);
            $table->index(['participant_id', 'started_at']);
            $table->index(['status', 'started_at']);
            $table->index(['provider', 'started_at']);
        });

        // Participant events table — tracks join/leave per participant for multi-user calls
        Schema::create('video_call_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_call_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name')->nullable();      // Guest name if not registered
            $table->string('role')->default('participant');  // host | participant | observer
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('mic_enabled')->default(true);
            $table->boolean('camera_enabled')->default(true);
            $table->json('meta')->nullable();                // device info, IP, etc.
            $table->timestamps();

            $table->index(['video_call_log_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_call_participants');
        Schema::dropIfExists('video_call_logs');
    }
};
