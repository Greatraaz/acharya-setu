<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->nullable()->default(0)->after('price_yearly');
            }
            if (! Schema::hasColumn('plans', 'discount_expires_at')) {
                $table->date('discount_expires_at')->nullable()->after('discount_percent');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            foreach (['discount_percent', 'discount_expires_at'] as $column) {
                if (Schema::hasColumn('plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
