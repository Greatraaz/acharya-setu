<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_streams', function (Blueprint $table) {
            $table->unsignedBigInteger('mentee_id')->nullable()->after('id');
            $table->unsignedBigInteger('mentor_id')->nullable()->after('mentee_id');

            $table->foreign('mentee_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('mentor_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->index(['mentor_id', 'mentee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('education_streams', function (Blueprint $table) {
            $table->dropForeign(['mentee_id']);
            $table->dropForeign(['mentor_id']);
            $table->dropColumn(['mentee_id', 'mentor_id']);
        });
    }
};
