<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_video_watches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentee_id');
            $table->unsignedBigInteger('mentor_video_file_id');
            $table->timestamp('watched_at');
            $table->timestamps();

            $table->unique(['mentee_id', 'mentor_video_file_id']);
            $table->foreign('mentee_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('mentor_video_file_id')->references('id')->on('mentor_video_files')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_video_watches');
    }
};
