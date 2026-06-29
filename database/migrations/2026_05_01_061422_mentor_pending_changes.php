<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Mentor pending profile changes (approval queue) ────
        Schema::create('mentor_pending_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_id');
            $table->json('changes');                        // proposed field values
            $table->string('status')->default('pending');  // pending|approved|rejected
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('mentor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['mentor_id', 'status']);
        });

        // ── Add approval-related columns to users ─────────────
        Schema::table('users', function (Blueprint $table) {
            // Onboarding progress  (already exist — skip if they do)
            // onboarding_step, onboarding_completed are already in users schema

            // Approval audit
            $table->unsignedBigInteger('approved_by')->nullable()->after('mentor_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');

            // Pending change flag
            $table->boolean('has_pending_changes')->default(false)->after('rejection_reason');

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'rejection_reason', 'has_pending_changes']);
        });
        Schema::dropIfExists('mentor_pending_changes');
    }
};