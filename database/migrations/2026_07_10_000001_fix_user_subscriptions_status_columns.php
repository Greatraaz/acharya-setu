<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_subscriptions')) {
            return;
        }

        if (Schema::hasColumn('user_subscriptions', 'status')) {
            DB::statement(
                "ALTER TABLE `user_subscriptions`
                 MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'pending'"
            );
        }

        if (Schema::hasColumn('user_subscriptions', 'payment_status')) {
            DB::statement(
                "ALTER TABLE `user_subscriptions`
                 MODIFY `payment_status` VARCHAR(32) NOT NULL DEFAULT 'pending'"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_subscriptions')) {
            return;
        }

        if (Schema::hasColumn('user_subscriptions', 'status')) {
            DB::statement(
                "ALTER TABLE `user_subscriptions`
                 MODIFY `status` ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active'"
            );
        }

        if (Schema::hasColumn('user_subscriptions', 'payment_status')) {
            DB::statement(
                "ALTER TABLE `user_subscriptions`
                 MODIFY `payment_status` ENUM('paid','failed','refunded') NOT NULL DEFAULT 'paid'"
            );
        }
    }
};
