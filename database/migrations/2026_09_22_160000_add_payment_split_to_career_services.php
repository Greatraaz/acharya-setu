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
                if (! Schema::hasColumn('career_service_requests', 'payment_method')) {
                    $table->string('payment_method', 20)->nullable()->after('payment_status');
                }
                if (! Schema::hasColumn('career_service_requests', 'wallet_amount')) {
                    $table->decimal('wallet_amount', 10, 2)->default(0)->after('payment_method');
                }
                if (! Schema::hasColumn('career_service_requests', 'razorpay_amount')) {
                    $table->decimal('razorpay_amount', 10, 2)->default(0)->after('wallet_amount');
                }
                if (! Schema::hasColumn('career_service_requests', 'payment_reference')) {
                    $table->string('payment_reference')->nullable()->after('razorpay_payment_id');
                }
            });
        }

        if (Schema::hasTable('career_service_invoices')) {
            Schema::table('career_service_invoices', function (Blueprint $table) {
                if (! Schema::hasColumn('career_service_invoices', 'wallet_amount')) {
                    $table->decimal('wallet_amount', 10, 2)->default(0)->after('base_amount');
                }
                if (! Schema::hasColumn('career_service_invoices', 'razorpay_amount')) {
                    $table->decimal('razorpay_amount', 10, 2)->default(0)->after('wallet_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('career_service_requests')) {
            Schema::table('career_service_requests', function (Blueprint $table) {
                foreach (['payment_method', 'wallet_amount', 'razorpay_amount', 'payment_reference'] as $col) {
                    if (Schema::hasColumn('career_service_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('career_service_invoices')) {
            Schema::table('career_service_invoices', function (Blueprint $table) {
                foreach (['wallet_amount', 'razorpay_amount'] as $col) {
                    if (Schema::hasColumn('career_service_invoices', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
