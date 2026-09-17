<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plans') && ! Schema::hasColumn('plans', 'igst_percent')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->decimal('igst_percent', 5, 2)->nullable()->after('sgst_percent');
            });
        }

        if (Schema::hasTable('plan_invoices')) {
            Schema::table('plan_invoices', function (Blueprint $table) {
                if (! Schema::hasColumn('plan_invoices', 'igst_percent')) {
                    $table->decimal('igst_percent', 5, 2)->default(0)->after('sgst_percent');
                }
                if (! Schema::hasColumn('plan_invoices', 'igst_amount')) {
                    $table->decimal('igst_amount', 10, 2)->default(0)->after('sgst_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'igst_percent')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('igst_percent');
            });
        }

        if (Schema::hasTable('plan_invoices')) {
            Schema::table('plan_invoices', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('plan_invoices', 'igst_percent')) {
                    $cols[] = 'igst_percent';
                }
                if (Schema::hasColumn('plan_invoices', 'igst_amount')) {
                    $cols[] = 'igst_amount';
                }
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
