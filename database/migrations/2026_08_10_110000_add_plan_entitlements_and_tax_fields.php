<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'progress_report_enabled')) {
                $table->boolean('progress_report_enabled')->default(false)->after('limits');
            }
            if (! Schema::hasColumn('plans', 'cgst_percent')) {
                $table->decimal('cgst_percent', 5, 2)->nullable()->after('currency');
            }
            if (! Schema::hasColumn('plans', 'sgst_percent')) {
                $table->decimal('sgst_percent', 5, 2)->nullable()->after('cgst_percent');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            foreach ([
                'progress_report_enabled',
                'cgst_percent',
                'sgst_percent',
            ] as $column) {
                if (Schema::hasColumn('plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
