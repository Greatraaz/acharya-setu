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
        Schema::create('consultation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
 
            // Scheduling
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('timezone')->default('Asia/Kolkata');
 
            // Session details
            $table->string('title');                             // "Career Guidance Session"
            $table->text('agenda')->nullable();                  // What mentee wants to discuss
            $table->text('mentor_notes')->nullable();            // Private mentor notes
            $table->string('meeting_link')->nullable();          // Zoom/Google/Agora link
            $table->string('meeting_provider')->nullable();      // agora|zoom|google
            $table->string('meeting_channel')->nullable();       // provider channel id
 
            // Status lifecycle
            // pending → confirmed → ongoing → completed | cancelled | no_show
            $table->string('status')->default('pending');
            $table->string('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('actual_duration_seconds')->nullable();
 
            // Payment
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 5)->default('INR');
            $table->string('payment_status')->default('pending'); // pending|paid|refunded|waived
            $table->string('payment_reference')->nullable();
 
            // Booking reference
            $table->string('booking_ref')->unique();             // e.g. SES-20240101-0042
 
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['mentor_id', 'scheduled_at']);
            $table->index(['mentee_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_sessions');
    }
};
