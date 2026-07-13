<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('task_supporting_materials')) {
            return;
        }

        Schema::table('task_supporting_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('task_supporting_materials', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('task_supporting_materials')) {
            return;
        }

        Schema::table('task_supporting_materials', function (Blueprint $table) {
            if (Schema::hasColumn('task_supporting_materials', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
