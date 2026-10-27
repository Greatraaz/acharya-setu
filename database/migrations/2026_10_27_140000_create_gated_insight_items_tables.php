<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gated_insight_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('title');
            $table->string('slug');
            $table->string('status', 20)->default('active');
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['type', 'slug']);
            $table->index(['type', 'status']);
        });

        Schema::create('gated_insight_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gated_insight_item_id')->constrained('gated_insight_items')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('organization')->nullable();
            $table->string('role', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['gated_insight_item_id', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gated_insight_downloads');
        Schema::dropIfExists('gated_insight_items');
    }
};
