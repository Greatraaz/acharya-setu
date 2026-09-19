<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consultation_sessions')) {
            return;
        }

        Schema::table('consultation_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_sessions', 'list_amount')) {
                $table->decimal('list_amount', 10, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('consultation_sessions', 'platform_subsidy')) {
                $table->decimal('platform_subsidy', 10, 2)->default(0)->after('coupon_discount');
            }
        });

        // Backfill: paid sessions list = mentee paid + coupon; plan/free stay 0 until rebooked.
        if (Schema::hasColumn('consultation_sessions', 'list_amount')) {
            DB::statement('UPDATE consultation_sessions SET list_amount = ROUND(COALESCE(amount, 0) + COALESCE(coupon_discount, 0), 2) WHERE list_amount = 0 AND (COALESCE(amount, 0) + COALESCE(coupon_discount, 0)) > 0');
            DB::statement('UPDATE consultation_sessions SET platform_subsidy = ROUND(COALESCE(coupon_discount, 0), 2) WHERE platform_subsidy = 0 AND COALESCE(coupon_discount, 0) > 0');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('consultation_sessions')) {
            return;
        }

        Schema::table('consultation_sessions', function (Blueprint $table) {
            foreach (['platform_subsidy', 'list_amount'] as $column) {
                if (Schema::hasColumn('consultation_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
