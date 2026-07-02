<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_months', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculum_months', 'mentee_id')) {
                $table->unsignedBigInteger('mentee_id')->nullable()->after('stream_id');
                $table->index(['stream_id', 'mentee_id']);
                $table->foreign('mentee_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::table('curriculum_weeks', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculum_weeks', 'mentee_id')) {
                $table->unsignedBigInteger('mentee_id')->nullable()->after('month_id');
                $table->index(['month_id', 'mentee_id']);
                $table->foreign('mentee_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::table('curriculum_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculum_tasks', 'mentee_id')) {
                $table->unsignedBigInteger('mentee_id')->nullable()->after('week_id');
                $table->index(['week_id', 'mentee_id']);
                $table->foreign('mentee_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        DB::statement("
            UPDATE curriculum_months cm
            JOIN education_streams es ON es.id = cm.stream_id
            SET cm.mentee_id = es.mentee_id
            WHERE cm.mentee_id IS NULL
        ");

        DB::statement("
            UPDATE curriculum_weeks cw
            JOIN curriculum_months cm ON cm.id = cw.month_id
            SET cw.mentee_id = cm.mentee_id
            WHERE cw.mentee_id IS NULL
        ");

        DB::statement("
            UPDATE curriculum_tasks ct
            JOIN curriculum_weeks cw ON cw.id = ct.week_id
            SET ct.mentee_id = cw.mentee_id
            WHERE ct.mentee_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('curriculum_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_tasks', 'mentee_id')) {
                $table->dropForeign(['mentee_id']);
                $table->dropIndex('curriculum_tasks_week_id_mentee_id_index');
                $table->dropColumn('mentee_id');
            }
        });

        Schema::table('curriculum_weeks', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_weeks', 'mentee_id')) {
                $table->dropForeign(['mentee_id']);
                $table->dropIndex('curriculum_weeks_month_id_mentee_id_index');
                $table->dropColumn('mentee_id');
            }
        });

        Schema::table('curriculum_months', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_months', 'mentee_id')) {
                $table->dropForeign(['mentee_id']);
                $table->dropIndex('curriculum_months_stream_id_mentee_id_index');
                $table->dropColumn('mentee_id');
            }
        });
    }
};
