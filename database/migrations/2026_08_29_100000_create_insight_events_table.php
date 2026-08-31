<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // webinar|event
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('speaker');
            $table->string('location');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->longText('event_agenda')->nullable();
            $table->longText('who_should_attend')->nullable();
            $table->longText('what_you_will_learn')->nullable();
            $table->text('faq')->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index('start_date');
        });

        Schema::create('insight_event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insight_event_id')->constrained('insight_events')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone', 30)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['insight_event_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_event_registrations');
        Schema::dropIfExists('insight_events');
    }
};
