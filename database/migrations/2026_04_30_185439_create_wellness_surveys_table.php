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
        Schema::create('wellness_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('wellness_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('wellness_surveys')->cascadeOnDelete();
            $table->text('question');
            $table->enum('type', ['scale', 'text', 'multiple_choice', 'yes_no'])->default('scale');
            $table->json('options')->nullable(); // for multiple_choice
            $table->integer('order')->default(0);
            $table->boolean('required')->default(true);
            $table->timestamps();
        });

        Schema::create('wellness_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('wellness_surveys')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['survey_id', 'user_id']);
        });

        Schema::create('wellness_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('wellness_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('wellness_questions')->cascadeOnDelete();
            $table->text('answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_surveys');
        Schema::dropIfExists('wellness_questions');
        Schema::dropIfExists('wellness_responses');
        Schema::dropIfExists('wellness_answers');
    }
};
