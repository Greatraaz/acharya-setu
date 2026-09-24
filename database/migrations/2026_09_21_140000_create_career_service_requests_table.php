<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('career_service_requests')) {
            return;
        }

        Schema::create('career_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // resume | linkedin
            $table->string('status', 30)->default('submitted'); // pending_payment | submitted | completed | cancelled
            $table->string('linkedin_url', 500)->nullable();
            $table->string('resume_path')->nullable();
            $table->text('mentee_notes')->nullable();
            $table->boolean('is_paid_addon')->default(false);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->string('payment_status', 20)->default('free'); // free | pending | paid | failed
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('plan_slug')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('deliverable_path')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_service_requests');
    }
};
