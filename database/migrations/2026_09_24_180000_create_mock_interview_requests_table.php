<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mock_interview_requests')) {
            return;
        }

        Schema::create('mock_interview_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('submitted');
            // pending_payment | submitted | confirmed | completed | cancelled

            $table->unsignedSmallInteger('duration_minutes');
            $table->dateTime('preferred_at');
            $table->dateTime('confirmed_at')->nullable();
            $table->string('timezone', 64)->default('Asia/Kolkata');

            $table->string('target_role', 255)->nullable();
            $table->text('mentee_notes')->nullable();

            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            $table->boolean('is_paid_addon')->default(false);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('rate_per_minute', 10, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('payment_status', 20)->default('free');
            $table->string('payment_method', 20)->nullable();
            $table->decimal('wallet_amount', 10, 2)->default(0);
            $table->decimal('razorpay_amount', 10, 2)->default(0);
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('plan_slug')->nullable();

            $table->text('admin_notes')->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'preferred_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_interview_requests');
    }
};
