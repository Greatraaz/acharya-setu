<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'name') && Schema::hasColumn('plans', 'plan_name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('plans', 'plan_name') && Schema::hasColumn('plans', 'name')) {
                $table->string('plan_name')->nullable()->after('id');
            }
        });

        if (Schema::hasColumn('plans', 'name') && Schema::hasColumn('plans', 'plan_name')) {
            DB::statement('UPDATE plans SET name = plan_name WHERE name IS NULL AND plan_name IS NOT NULL');
            DB::statement('UPDATE plans SET plan_name = name WHERE plan_name IS NULL AND name IS NOT NULL');
        }
    }

    public function down(): void
    {
        // Keep both columns for backward compatibility.
    }
};
