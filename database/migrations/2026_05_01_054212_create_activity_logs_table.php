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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
 
            // Who performed the action
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable();          // App\Models\User (polymorphic)
            $table->string('causer_name')->nullable();          // denormalized for soft-deleted users
 
            // What was acted upon
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();         // App\Models\User, ConsultationSession…
            $table->string('subject_label')->nullable();        // denormalized display label
 
            // Action details
            $table->string('event');                            // created|updated|deleted|login|logout|payment…
            $table->string('description');                      // Human-readable sentence
            $table->string('module')->nullable();               // users|sessions|payments|curriculum|auth…
            $table->string('level')->default('info');           // info|warning|danger|success
 
            // Request context
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();               // GET|POST|PUT|DELETE
 
            // Diff (what changed)
            $table->json('properties')->nullable();             // ['old'=>[…], 'new'=>[…], 'meta'=>[…]]
 
            $table->timestamp('logged_at');
            $table->timestamps();
 
            // Indexes for fast filtering
            $table->index(['causer_id', 'causer_type']);
            $table->index(['subject_id', 'subject_type']);
            $table->index(['event', 'module']);
            $table->index('level');
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
