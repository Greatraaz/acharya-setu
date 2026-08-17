<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessments') && ! Schema::hasColumn('assessments', 'status')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('instructions');
            });
        }

        if (Schema::hasTable('assessment_questions') && Schema::hasColumn('assessment_questions', 'category_id')) {
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'status')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
