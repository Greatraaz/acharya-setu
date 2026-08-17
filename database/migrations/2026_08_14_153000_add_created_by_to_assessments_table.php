<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessments')) {
            return;
        }

        if (! Schema::hasColumn('assessments', 'created_by')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('questions')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'created_by')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
            });
        }
    }
};
