<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_subscriptions')) {
            Schema::create('user_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
                $table->string('subscription_id')->unique();
                $table->decimal('amount_paid', 10, 2)->default(0);
                $table->string('currency', 10)->default('INR');
                $table->string('payment_status')->default('pending'); // pending|paid|failed|refunded
                $table->string('payment_reference')->nullable();
                $table->string('razorpay_order_id')->nullable()->index();
                $table->string('razorpay_payment_id')->nullable();
                $table->string('status')->default('pending'); // pending|active|expired|cancelled
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['user_id', 'payment_status']);
            });

            return;
        }

        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('user_subscriptions', 'currency')) {
                $table->string('currency', 10)->default('INR')->after('amount_paid');
            }
            if (! Schema::hasColumn('user_subscriptions', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('currency');
            }
            if (! Schema::hasColumn('user_subscriptions', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('user_subscriptions', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->after('payment_reference');
            }
            if (! Schema::hasColumn('user_subscriptions', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            }
            if (! Schema::hasColumn('user_subscriptions', 'status')) {
                $table->string('status')->default('pending')->after('razorpay_payment_id');
            }
            if (! Schema::hasColumn('user_subscriptions', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('user_subscriptions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('starts_at');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('user_subscriptions')) {
            Schema::dropIfExists('user_subscriptions');
        }
    }
};
