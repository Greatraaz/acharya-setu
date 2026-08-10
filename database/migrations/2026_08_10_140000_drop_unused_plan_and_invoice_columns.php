<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                foreach ([
                    'gst_percent',
                    'igst_percent',
                    'tax_inclusive',
                    'hsn_sac_code',
                    'invoice_prefix',
                ] as $column) {
                    if (Schema::hasColumn('plans', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('plan_invoices') && Schema::hasColumn('plan_invoices', 'hsn_sac_code')) {
            Schema::table('plan_invoices', function (Blueprint $table) {
                $table->dropColumn('hsn_sac_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                if (! Schema::hasColumn('plans', 'gst_percent')) {
                    $table->decimal('gst_percent', 5, 2)->nullable()->after('currency');
                }
                if (! Schema::hasColumn('plans', 'igst_percent')) {
                    $table->decimal('igst_percent', 5, 2)->nullable()->after('sgst_percent');
                }
                if (! Schema::hasColumn('plans', 'tax_inclusive')) {
                    $table->boolean('tax_inclusive')->default(true)->after('sgst_percent');
                }
                if (! Schema::hasColumn('plans', 'hsn_sac_code')) {
                    $table->string('hsn_sac_code', 20)->nullable();
                }
                if (! Schema::hasColumn('plans', 'invoice_prefix')) {
                    $table->string('invoice_prefix', 30)->nullable();
                }
            });
        }

        if (Schema::hasTable('plan_invoices') && ! Schema::hasColumn('plan_invoices', 'hsn_sac_code')) {
            Schema::table('plan_invoices', function (Blueprint $table) {
                $table->string('hsn_sac_code')->nullable()->after('plan_name');
            });
        }
    }
};
