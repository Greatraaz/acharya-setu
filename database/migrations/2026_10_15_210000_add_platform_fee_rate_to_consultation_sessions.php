<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consultation_sessions') || Schema::hasColumn('consultation_sessions', 'platform_fee_rate')) {
            return;
        }

        Schema::table('consultation_sessions', function (Blueprint $table) {
            $table->decimal('platform_fee_rate', 6, 4)->nullable()->after('platform_subsidy');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('consultation_sessions') && Schema::hasColumn('consultation_sessions', 'platform_fee_rate')) {
            Schema::table('consultation_sessions', function (Blueprint $table) {
                $table->dropColumn('platform_fee_rate');
            });
        }
    }
};
