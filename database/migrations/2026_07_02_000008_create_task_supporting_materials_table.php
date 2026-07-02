<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_supporting_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('week_id');
            $table->unsignedBigInteger('mentee_id');
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->string('title')->nullable();
            $table->string('type'); // pdf|doc|image|videolink|ppt
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['task_id', 'mentee_id']);
            $table->index(['week_id', 'mentee_id']);

            $table->foreign('task_id')
                ->references('id')->on('curriculum_tasks')
                ->cascadeOnDelete();

            $table->foreign('week_id')
                ->references('id')->on('curriculum_weeks')
                ->cascadeOnDelete();

            $table->foreign('mentee_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('mentor_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_supporting_materials');
    }
};
