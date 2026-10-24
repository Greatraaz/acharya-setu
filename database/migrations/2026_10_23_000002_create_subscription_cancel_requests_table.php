<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_cancel_requests')) {
            return;
        }

        Schema::create('subscription_cancel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_subscription_id')->constrained('user_subscriptions')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->text('reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('auto_approve_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approved_by')->nullable(); // admin|auto
            $table->unsignedTinyInteger('consumed_months')->default(0);
            $table->unsignedTinyInteger('refund_months')->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('monthly_amount', 10, 2)->default(0);
            $table->decimal('consumed_amount', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('refund_destination')->nullable(); // razorpay|wallet
            $table->decimal('razorpay_refunded', 10, 2)->default(0);
            $table->decimal('wallet_refunded', 10, 2)->default(0);
            $table->string('razorpay_refund_id')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'auto_approve_at']);
            $table->index(['user_id', 'status']);
            $table->index('user_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_cancel_requests');
    }
};
