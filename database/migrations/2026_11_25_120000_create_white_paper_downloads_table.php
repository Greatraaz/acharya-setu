<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('white_paper_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('white_paper_id')->constrained('white_papers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('organization')->nullable();
            $table->string('role', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['white_paper_id', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('white_paper_downloads');
    }
};
