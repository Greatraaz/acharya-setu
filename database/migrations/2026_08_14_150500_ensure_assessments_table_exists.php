<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessments')) {
            return;
        }

        Schema::create('assessments', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('title');
            $table->integer('month');
            $table->text('description')->nullable();
            $table->json('questions')->nullable();
            $table->timestamps();
            $table->unique('month');
            $table->index('month');
        });
    }

    public function down(): void
    {
        // Do not drop — table may have existed before this repair migration.
    }
};
