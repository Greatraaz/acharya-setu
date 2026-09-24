<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('career_service_invoices')) {
            return;
        }

        Schema::create('career_service_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_service_request_id')->unique()->constrained('career_service_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->string('billing_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('service_type'); // resume | linkedin
            $table->string('description');
            $table->string('payment_method')->default('razorpay'); // razorpay | plan | free
            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('INR');
            $table->string('payment_reference')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('plan_slug')->nullable();
            $table->string('seller_name')->nullable();
            $table->string('seller_gstin')->nullable();
            $table->text('seller_address')->nullable();
            $table->string('seller_email')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('status')->default('issued');
            $table->string('generated_by')->default('system');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'invoice_date']);
            $table->index('service_type');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_service_invoices');
    }
};
