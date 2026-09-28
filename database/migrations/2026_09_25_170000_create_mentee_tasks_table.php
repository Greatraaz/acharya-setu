<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentee_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 32)->default('task');
            $table->string('submission_type', 32)->default('none');
            $table->json('attachments')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['mentee_id', 'is_active']);
            $table->index(['mentor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentee_tasks');
    }
};
