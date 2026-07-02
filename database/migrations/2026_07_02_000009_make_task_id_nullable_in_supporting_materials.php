<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('task_supporting_materials') || !Schema::hasColumn('task_supporting_materials', 'task_id')) {
            return;
        }

        Schema::table('task_supporting_materials', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
        });

        Schema::table('task_supporting_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable()->change();
            $table->foreign('task_id')
                ->references('id')->on('curriculum_tasks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('task_supporting_materials') || !Schema::hasColumn('task_supporting_materials', 'task_id')) {
            return;
        }

        Schema::table('task_supporting_materials', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
        });

        Schema::table('task_supporting_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable(false)->change();
            $table->foreign('task_id')
                ->references('id')->on('curriculum_tasks')
                ->cascadeOnDelete();
        });
    }
};
