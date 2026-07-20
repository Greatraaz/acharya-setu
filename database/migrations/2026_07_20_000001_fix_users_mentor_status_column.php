<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'mentor_status')) {
            return;
        }

        // Legacy enum used 'cancelled'; app expects 'rejected' and 'suspended'.
        DB::table('users')
            ->where('mentor_status', 'cancelled')
            ->update(['mentor_status' => 'rejected']);

        DB::statement(
            "ALTER TABLE `users`
             MODIFY `mentor_status` VARCHAR(32) NOT NULL DEFAULT 'approved'"
        );
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'mentor_status')) {
            return;
        }

        DB::table('users')
            ->whereIn('mentor_status', ['rejected', 'suspended'])
            ->update(['mentor_status' => 'cancelled']);

        DB::statement(
            "ALTER TABLE `users`
             MODIFY `mentor_status` ENUM('pending','approved','cancelled') NOT NULL DEFAULT 'approved'"
        );
    }
};
