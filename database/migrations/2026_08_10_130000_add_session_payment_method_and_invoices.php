<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_sessions', 'payment_method')) {
                $table->string('payment_method', 30)->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('consultation_sessions', 'wallet_amount')) {
                $table->decimal('wallet_amount', 10, 2)->default(0)->after('payment_method');
            }
            if (! Schema::hasColumn('consultation_sessions', 'razorpay_amount')) {
                $table->decimal('razorpay_amount', 10, 2)->default(0)->after('wallet_amount');
            }
        });

        if (! Schema::hasTable('session_invoices')) {
            Schema::create('session_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consultation_session_id')->unique()->constrained('consultation_sessions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('invoice_number')->unique();
                $table->date('invoice_date');
                $table->string('billing_name')->nullable();
                $table->string('billing_email')->nullable();
                $table->string('billing_phone')->nullable();
                $table->string('description')->nullable();
                $table->string('payment_method', 30)->nullable();
                $table->decimal('base_amount', 10, 2)->default(0);
                $table->decimal('wallet_amount', 10, 2)->default(0);
                $table->decimal('razorpay_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->string('currency', 10)->default('INR');
                $table->string('payment_reference')->nullable();
                $table->string('razorpay_order_id')->nullable();
                $table->string('razorpay_payment_id')->nullable();
                $table->string('booking_ref')->nullable();
                $table->timestamp('session_at')->nullable();
                $table->unsignedInteger('duration_minutes')->nullable();
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
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('session_invoices');

        Schema::table('consultation_sessions', function (Blueprint $table) {
            foreach (['razorpay_amount', 'wallet_amount', 'payment_method'] as $col) {
                if (Schema::hasColumn('consultation_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
