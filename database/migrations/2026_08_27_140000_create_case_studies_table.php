<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('industry');
            $table->string('status', 20)->default('active'); // active|inactive
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->longText('result')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('industry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_studies');
    }
};
