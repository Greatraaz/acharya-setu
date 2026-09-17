<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_subscriptions') && ! Schema::hasColumn('user_subscriptions', 'meta')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $table->json('meta')->nullable()->after('expires_at');
            });
        }

        if (Schema::hasTable('plan_invoices')) {
            Schema::table('plan_invoices', function (Blueprint $table) {
                try {
                    $table->dropForeign(['user_subscription_id']);
                } catch (\Throwable) {
                    // Already dropped or named differently.
                }
            });

            Schema::table('plan_invoices', function (Blueprint $table) {
                try {
                    $table->dropUnique(['user_subscription_id']);
                } catch (\Throwable) {
                    // Index name can differ by engine; ignore if already dropped.
                }
            });

            Schema::table('plan_invoices', function (Blueprint $table) {
                $table->foreign('user_subscription_id')
                    ->references('id')
                    ->on('user_subscriptions')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_subscriptions') && Schema::hasColumn('user_subscriptions', 'meta')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $table->dropColumn('meta');
            });
        }
    }
};
