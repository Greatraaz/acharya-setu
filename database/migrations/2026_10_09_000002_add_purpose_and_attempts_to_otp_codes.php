<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('otp_codes', 'purpose')) {
                $table->string('purpose', 32)->default('registration')->after('channel');
            }
            if (! Schema::hasColumn('otp_codes', 'attempts')) {
                $table->unsignedTinyInteger('attempts')->default(0)->after('code');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE otp_codes MODIFY expires_at TIMESTAMP NULL DEFAULT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            if (Schema::hasColumn('otp_codes', 'attempts')) {
                $table->dropColumn('attempts');
            }
            if (Schema::hasColumn('otp_codes', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }
};
