<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_guides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('active');
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        Schema::create('mentorship_guide_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentorship_guide_id')->constrained('mentorship_guides')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('organization')->nullable();
            $table->string('role', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['mentorship_guide_id', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_guide_downloads');
        Schema::dropIfExists('mentorship_guides');
    }
};
