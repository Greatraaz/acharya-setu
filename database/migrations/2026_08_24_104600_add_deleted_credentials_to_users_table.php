<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'deleted_email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('deleted_email')->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('users', 'deleted_phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('deleted_phone')->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'deleted_email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('deleted_email');
            });
        }

        if (Schema::hasColumn('users', 'deleted_phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('deleted_phone');
            });
        }
    }
};
