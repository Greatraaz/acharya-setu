<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'mentee'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')->where('role', 'expert')->update(['role' => 'mentor']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('mentor','mentee','admin') NOT NULL DEFAULT 'mentee'");
    }
};
