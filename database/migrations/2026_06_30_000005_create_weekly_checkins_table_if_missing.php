<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('weekly_checkins')) {
            return;
        }

        Schema::create('weekly_checkins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentee_id');
            $table->unsignedBigInteger('week_id');
            $table->tinyInteger('mood_score')->nullable();
            $table->text('wins')->nullable();
            $table->text('challenges')->nullable();
            $table->text('questions')->nullable();
            $table->text('mentor_response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('mentor_replied_at')->nullable();
            $table->timestamps();

            $table->unique(['mentee_id', 'week_id']);

            $table->foreign('mentee_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('week_id')
                ->references('id')->on('curriculum_weeks')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_checkins');
    }
};
