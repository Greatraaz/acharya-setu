<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Education Streams ──────────────────────────────────
        Schema::create('education_streams', function (Blueprint $table) {
            $table->id();                               // bigint auto-increment
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
 
        // ── Curriculum Months (1–6) ────────────────────────────
        Schema::create('curriculum_months', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stream_id')->nullable();
            $table->integer('month_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('theme')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('learning_outcomes')->nullable();
            $table->string('milestone_badge')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
 
            $table->unique(['stream_id', 'month_number']);
            $table->foreign('stream_id')
                  ->references('id')->on('education_streams')
                  ->nullOnDelete();
        });
 
        // ── Curriculum Weeks (4 per month) ────────────────────
        Schema::create('curriculum_weeks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('month_id');
            $table->integer('week_number');
            $table->string('title');
            $table->text('focus')->nullable();
            $table->text('mentor_guide')->nullable();
            $table->json('resources')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
 
            $table->unique(['month_id', 'week_number']);
            $table->foreign('month_id')
                  ->references('id')->on('curriculum_months')
                  ->cascadeOnDelete();
        });
 
        // ── Curriculum Tasks ───────────────────────────────────
        Schema::create('curriculum_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('week_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('task');   // task|reading|video|project|quiz|reflection
            $table->integer('order_index')->default(0);
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('attachments')->nullable();
            $table->string('submission_type')->nullable(); // none|text|file|link
            $table->timestamps();
 
            $table->foreign('week_id')
                  ->references('id')->on('curriculum_weeks')
                  ->cascadeOnDelete();
        });
 
        // ── Curriculum MCQs ───────────────────────────────────
        Schema::create('curriculum_mcqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('week_id');
            $table->text('question');
            $table->json('options');
            $table->integer('correct_index');
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('points')->default(1);
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
 
            $table->foreign('week_id')
                  ->references('id')->on('curriculum_weeks')
                  ->cascadeOnDelete();
        });
 
        // ── Student Progress ───────────────────────────────────
        Schema::create('student_curriculum_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();  // FK → users.id (integer)
            $table->string('item_type');                        // 'task' | 'mcq'
            $table->unsignedBigInteger('item_id');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->string('submission_url')->nullable();
            $table->text('submission_text')->nullable();
            $table->string('submission_status')->default('none'); // none|submitted|reviewed|approved|rejected
            $table->text('mentor_feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
 
            $table->unique(['user_id', 'item_type', 'item_id']);
            $table->index(['user_id', 'item_type']);
 
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
 
        // ── MCQ Attempts ──────────────────────────────────────
        Schema::create('mcq_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mcq_id');
            $table->integer('selected_index');
            $table->boolean('is_correct');
            $table->integer('points_earned')->default(0);
            $table->timestamp('attempted_at');
            $table->timestamps();
 
            $table->index(['user_id', 'mcq_id']);
 
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
            $table->foreign('mcq_id')
                  ->references('id')->on('curriculum_mcqs')
                  ->cascadeOnDelete();
        });
 
        // ── Mentee Enrollments ─────────────────────────────────
        Schema::create('mentee_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentee_id');
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->unsignedBigInteger('stream_id');
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('status')->default('active');      // active|paused|completed|dropped
            $table->unsignedInteger('current_month')->default(1);
            $table->unsignedInteger('current_week')->default(1);
            $table->text('mentor_notes')->nullable();
            $table->timestamps();
 
            $table->unique(['mentee_id', 'stream_id']);
            $table->index(['mentor_id', 'status']);
 
            $table->foreign('mentee_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
            $table->foreign('mentor_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
            $table->foreign('stream_id')
                  ->references('id')->on('education_streams')
                  ->cascadeOnDelete();
        });
 
        // ── Weekly Check-ins ───────────────────────────────────
        Schema::create('weekly_checkins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentee_id');
            $table->unsignedBigInteger('week_id');
            $table->tinyInteger('mood_score')->nullable();
            $table->text('wins')->nullable();
            $table->text('challenges')->nullable();
            $table->text('questions')->nullable();
            $table->text('mentor_response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('mentor_replied_at')->nullable();
            $table->timestamps();
 
            $table->unique(['mentee_id', 'week_id']);
 
            $table->foreign('mentee_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
            $table->foreign('week_id')
                  ->references('id')->on('curriculum_weeks')
                  ->cascadeOnDelete();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('weekly_checkins');
        Schema::dropIfExists('mentee_enrollments');
        Schema::dropIfExists('mcq_attempts');
        Schema::dropIfExists('student_curriculum_progress');
        Schema::dropIfExists('curriculum_mcqs');
        Schema::dropIfExists('curriculum_tasks');
        Schema::dropIfExists('curriculum_weeks');
        Schema::dropIfExists('curriculum_months');
        Schema::dropIfExists('education_streams');
    }
};
