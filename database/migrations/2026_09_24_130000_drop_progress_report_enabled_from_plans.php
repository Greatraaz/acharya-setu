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

        if (Schema::hasColumn('plans', 'progress_report_enabled')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('progress_report_enabled');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        if (! Schema::hasColumn('plans', 'progress_report_enabled')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('progress_report_enabled')->default(false)->after('limits');
            });
        }
    }
};
