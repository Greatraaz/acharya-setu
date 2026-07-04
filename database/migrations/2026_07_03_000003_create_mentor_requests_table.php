<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentee_id');
            $table->unsignedBigInteger('mentor_id');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->text('mentor_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('mentee_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('mentor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['mentor_id', 'status']);
            $table->index(['mentee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_requests');
    }
};
