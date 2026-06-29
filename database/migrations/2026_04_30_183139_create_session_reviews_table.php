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
        Schema::create('session_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('consultation_sessions')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->string('reviewer_role');                     // mentor | mentee
 
            // Ratings (1-5 each)
            $table->unsignedTinyInteger('overall_rating');
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->unsignedTinyInteger('knowledge_rating')->nullable();
            $table->unsignedTinyInteger('punctuality_rating')->nullable();
            $table->unsignedTinyInteger('helpfulness_rating')->nullable();
 
            $table->text('review_text')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->boolean('is_public')->default(true);
            $table->timestamp('submitted_at')->nullable();
 
            $table->timestamps();
 
            // One review per role per session
            $table->unique(['session_id', 'reviewer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_reviews');
    }
};
