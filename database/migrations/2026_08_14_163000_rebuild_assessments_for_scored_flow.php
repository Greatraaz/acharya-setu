<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if (! Schema::hasColumn('assessments', 'image')) {
                    $table->string('image')->nullable()->after('description');
                }
                if (! Schema::hasColumn('assessments', 'icon')) {
                    $table->string('icon')->nullable()->after('image');
                }
                if (! Schema::hasColumn('assessments', 'instructions')) {
                    $table->longText('instructions')->nullable()->after('icon');
                }
            });

            $this->dropIndexIfExists('assessments', 'assessments_month_unique');
            $this->dropIndexIfExists('assessments', 'assessments_month_index');

            if (Schema::hasColumn('assessments', 'month')) {
                Schema::table('assessments', function (Blueprint $table) {
                    $table->dropColumn('month');
                });
            }

            if (Schema::hasColumn('assessments', 'questions')) {
                Schema::table('assessments', function (Blueprint $table) {
                    $table->dropColumn('questions');
                });
            }
        }

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

        if (! Schema::hasTable('assessment_questions')) {
            Schema::create('assessment_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('assessment_id');
                $table->unsignedBigInteger('category_id');
                $table->text('question');
                $table->json('options');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index('assessment_id');
                $table->index('category_id');
            });
        }

        if (! Schema::hasTable('assessment_score_bands')) {
            Schema::create('assessment_score_bands', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('assessment_id');
                $table->unsignedTinyInteger('band_index')->default(0);
                $table->unsignedInteger('range_from')->default(0);
                $table->unsignedInteger('range_to')->default(0);
                $table->string('heading')->nullable();
                $table->longText('description')->nullable();
                $table->timestamps();
                $table->index('assessment_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_score_bands');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessment_categories');
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropUnique($index);
            });
        } catch (\Throwable) {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($index) {
                    $blueprint->dropIndex($index);
                });
            } catch (\Throwable) {
                // Index may not exist on this environment.
            }
        }
    }
};
