<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_mcqs', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculum_mcqs', 'mentee_id')) {
                $table->unsignedBigInteger('mentee_id')->nullable()->after('week_id');
                $table->index(['week_id', 'mentee_id']);
                $table->foreign('mentee_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        DB::statement("
            UPDATE curriculum_mcqs cm
            JOIN curriculum_weeks cw ON cw.id = cm.week_id
            SET cm.mentee_id = cw.mentee_id
            WHERE cm.mentee_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('curriculum_mcqs', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_mcqs', 'mentee_id')) {
                $table->dropForeign(['mentee_id']);
                $table->dropIndex('curriculum_mcqs_week_id_mentee_id_index');
                $table->dropColumn('mentee_id');
            }
        });
    }
};
