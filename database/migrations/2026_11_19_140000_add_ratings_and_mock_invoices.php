<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('career_service_requests')) {
            Schema::table('career_service_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('career_service_requests', 'mentee_rating')) {
                    $table->unsignedTinyInteger('mentee_rating')->nullable()->after('completed_at');
                }
                if (! Schema::hasColumn('career_service_requests', 'mentee_review')) {
                    $table->text('mentee_review')->nullable()->after('mentee_rating');
                }
                if (! Schema::hasColumn('career_service_requests', 'rated_at')) {
                    $table->timestamp('rated_at')->nullable()->after('mentee_review');
                }
            });
        }

        if (Schema::hasTable('mock_interview_requests')) {
            Schema::table('mock_interview_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('mock_interview_requests', 'mentee_rating')) {
                    $table->unsignedTinyInteger('mentee_rating')->nullable()->after('completed_at');
                }
                if (! Schema::hasColumn('mock_interview_requests', 'mentee_review')) {
                    $table->text('mentee_review')->nullable()->after('mentee_rating');
                }
                if (! Schema::hasColumn('mock_interview_requests', 'rated_at')) {
                    $table->timestamp('rated_at')->nullable()->after('mentee_review');
                }
            });
        }

        if (! Schema::hasTable('mock_interview_invoices')) {
            Schema::create('mock_interview_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mock_interview_request_id')->unique()->constrained('mock_interview_requests')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('invoice_number')->unique();
                $table->date('invoice_date');
                $table->string('billing_name')->nullable();
                $table->string('billing_email')->nullable();
                $table->string('billing_phone')->nullable();
                $table->string('description');
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->string('payment_method')->default('razorpay');
                $table->decimal('base_amount', 10, 2)->default(0);
                $table->decimal('wallet_amount', 10, 2)->default(0);
                $table->decimal('razorpay_amount', 10, 2)->default(0);
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
                $table->index('payment_method');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_interview_invoices');

        if (Schema::hasTable('career_service_requests')) {
            Schema::table('career_service_requests', function (Blueprint $table) {
                foreach (['rated_at', 'mentee_review', 'mentee_rating'] as $col) {
                    if (Schema::hasColumn('career_service_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('mock_interview_requests')) {
            Schema::table('mock_interview_requests', function (Blueprint $table) {
                foreach (['rated_at', 'mentee_review', 'mentee_rating'] as $col) {
                    if (Schema::hasColumn('mock_interview_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
