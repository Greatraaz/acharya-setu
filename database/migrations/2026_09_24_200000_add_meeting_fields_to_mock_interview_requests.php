<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mock_interview_requests')) {
            return;
        }

        Schema::table('mock_interview_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('mock_interview_requests', 'meeting_channel')) {
                $table->string('meeting_channel', 32)->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('mock_interview_requests', 'meeting_link')) {
                $table->string('meeting_link', 500)->nullable()->after('meeting_channel');
            }
            if (! Schema::hasColumn('mock_interview_requests', 'meeting_provider')) {
                $table->string('meeting_provider', 20)->nullable()->after('meeting_link');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mock_interview_requests')) {
            return;
        }

        Schema::table('mock_interview_requests', function (Blueprint $table) {
            foreach (['meeting_channel', 'meeting_link', 'meeting_provider'] as $column) {
                if (Schema::hasColumn('mock_interview_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
