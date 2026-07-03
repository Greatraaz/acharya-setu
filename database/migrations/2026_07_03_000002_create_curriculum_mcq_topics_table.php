<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_mcq_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('week_id');
            $table->unsignedBigInteger('mentee_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('week_id')
                ->references('id')->on('curriculum_weeks')
                ->cascadeOnDelete();
            $table->foreign('mentee_id')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->index(['week_id', 'mentee_id']);
        });

        Schema::table('curriculum_mcqs', function (Blueprint $table) {
            if (! Schema::hasColumn('curriculum_mcqs', 'topic_id')) {
                $table->unsignedBigInteger('topic_id')->nullable()->after('week_id');
                $table->foreign('topic_id')
                    ->references('id')->on('curriculum_mcq_topics')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_mcqs', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_mcqs', 'topic_id')) {
                $table->dropForeign(['topic_id']);
                $table->dropColumn('topic_id');
            }
        });

        Schema::dropIfExists('curriculum_mcq_topics');
    }
};
