<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_interview_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_interview_request_id')
                ->constrained('mock_interview_requests')
                ->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->default('note'); // note | resource | action_item
            $table->text('content');
            $table->string('resource_url')->nullable();
            $table->boolean('is_shared')->default(false); // shared with other party
            $table->timestamps();

            $table->index(['mock_interview_request_id', 'is_shared'], 'mi_notes_request_shared_idx');
            $table->index(['mock_interview_request_id', 'author_id', 'is_shared'], 'mi_notes_request_author_shared_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_interview_notes');
    }
};
