<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            $table->boolean('forfeit_plan_benefit')->default(false)->after('platform_subsidy');
            $table->json('cancellation_settlement')->nullable()->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            $table->dropColumn(['forfeit_plan_benefit', 'cancellation_settlement']);
        });
    }
};
