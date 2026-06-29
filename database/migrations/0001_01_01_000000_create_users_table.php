<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['mentor','mentee','admin'])->default('mentee');
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->text('bio')->nullable();
            $table->json('expertise')->nullable();
            $table->string('field')->nullable();
            $table->string('college')->nullable();
            $table->string('year')->nullable();
            $table->string('gender')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_sessions')->default(0);
            $table->text('avatar_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('company')->nullable();
            $table->string('designation')->nullable();
            $table->integer('experience_years')->default(0);
            $table->boolean('is_active')->default(true);
            $table->decimal('rate_per_minute', 8, 2)->default(10);
            $table->uuid('assigned_mentor_id')->nullable();
            $table->string('subscription_plan')->default('free');
            $table->string('mentor_status')->default('approved');
            $table->string('education_stream')->nullable();
            $table->json('career_goals')->nullable();
            $table->json('strengths')->nullable();
            $table->json('preferences')->nullable();
            $table->integer('onboarding_step')->default(0);
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('role');
            $table->index('assigned_mentor_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
