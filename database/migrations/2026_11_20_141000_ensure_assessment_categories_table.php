<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessment_categories')) {
            Schema::create('assessment_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('assessment_id');
                $table->string('name');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index('assessment_id');
            });
        }

        if (Schema::hasTable('assessment_questions') && Schema::hasColumn('assessment_questions', 'category_id')) {
            // Allow questions without a category group (admin can attach later).
            try {
                \Illuminate\Support\Facades\DB::statement(
                    'ALTER TABLE `assessment_questions` MODIFY `category_id` BIGINT UNSIGNED NULL'
                );
            } catch (\Throwable) {
                // Already nullable or unsupported engine tweak.
            }
        }
    }

    public function down(): void
    {
        // Keep table — destructive drop not needed for this hotfix.
    }
};
