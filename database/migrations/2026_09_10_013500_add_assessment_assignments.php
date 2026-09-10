<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessments') && ! Schema::hasColumn('assessments', 'assign_to_all')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->boolean('assign_to_all')->default(true)->after('created_by');
            });
        }

        if (! Schema::hasTable('assessment_assignments')) {
            Schema::create('assessment_assignments', function (Blueprint $table) {
                $table->id();
                // assessments.id is signed INT(11), not unsigned
                $table->integer('assessment_id');
                $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['assessment_id', 'mentee_id']);
                $table->index('mentee_id');
                $table->index('assessment_id');
            });
        } else {
            // Repair partial create from earlier failed FK attempt
            $column = collect(DB::select('SHOW COLUMNS FROM assessment_assignments WHERE Field = "assessment_id"'))->first();
            if ($column && str_contains(strtolower((string) $column->Type), 'unsigned')) {
                DB::statement('ALTER TABLE assessment_assignments MODIFY assessment_id INT(11) NOT NULL');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_assignments');

        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'assign_to_all')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropColumn('assign_to_all');
            });
        }
    }
};
