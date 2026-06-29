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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('department')->nullable();        // Engineering, Marketing, Design
            $table->string('location');                      // Remote, Mumbai, Hybrid
            $table->string('location_type')->default('onsite'); // onsite | remote | hybrid
            $table->string('job_type')->default('full_time'); // full_time | part_time | contract | internship | freelance
            $table->string('experience_level')->default('mid'); // entry | mid | senior | lead | executive
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 10)->default('INR');
            $table->string('salary_period')->default('yearly'); // monthly | yearly
            $table->boolean('salary_hidden')->default(false);   // "Competitive" instead of showing number
            $table->longText('description');
            $table->longText('responsibilities')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('benefits')->nullable();
            $table->json('skills')->nullable();              // ["PHP","Laravel","MySQL"]
            $table->string('apply_url')->nullable();         // External apply link
            $table->string('apply_email')->nullable();       // HR email
            $table->date('deadline')->nullable();            // Application deadline
            $table->unsignedInteger('openings')->default(1); // Number of openings
            $table->unsignedInteger('applications_count')->default(0);
            $table->string('status')->default('draft');      // draft | active | paused | closed
            $table->boolean('is_featured')->default(false);
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['status', 'published_at']);
            $table->index(['department', 'status']);
            $table->index('location_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
