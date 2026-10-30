<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_streams', function (Blueprint $table) {
            if (! Schema::hasColumn('education_streams', 'is_template')) {
                $table->boolean('is_template')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('education_streams', 'expertise_stream')) {
                $table->string('expertise_stream')->nullable()->after('is_template');
            }
            if (! Schema::hasColumn('education_streams', 'expertise_field')) {
                $table->string('expertise_field')->nullable()->after('expertise_stream');
            }
            if (! Schema::hasColumn('education_streams', 'expertise_tags')) {
                $table->json('expertise_tags')->nullable()->after('expertise_field');
            }
            if (! Schema::hasColumn('education_streams', 'cloned_from_id')) {
                $table->unsignedBigInteger('cloned_from_id')->nullable()->after('expertise_tags');
            }
        });

        Schema::table('education_streams', function (Blueprint $table) {
            if (Schema::hasColumn('education_streams', 'is_template')) {
                $table->index(['is_template', 'is_active'], 'education_streams_template_active_index');
            }
            if (Schema::hasColumn('education_streams', 'expertise_stream')) {
                $table->index('expertise_stream');
            }
            if (Schema::hasColumn('education_streams', 'cloned_from_id')) {
                $table->foreign('cloned_from_id')
                    ->references('id')
                    ->on('education_streams')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('education_streams', function (Blueprint $table) {
            if (Schema::hasColumn('education_streams', 'cloned_from_id')) {
                $table->dropForeign(['cloned_from_id']);
            }
            if (Schema::hasIndex('education_streams', 'education_streams_template_active_index')) {
                $table->dropIndex('education_streams_template_active_index');
            }
            $drops = array_values(array_filter([
                Schema::hasColumn('education_streams', 'cloned_from_id') ? 'cloned_from_id' : null,
                Schema::hasColumn('education_streams', 'expertise_tags') ? 'expertise_tags' : null,
                Schema::hasColumn('education_streams', 'expertise_field') ? 'expertise_field' : null,
                Schema::hasColumn('education_streams', 'expertise_stream') ? 'expertise_stream' : null,
                Schema::hasColumn('education_streams', 'is_template') ? 'is_template' : null,
            ]));
            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
