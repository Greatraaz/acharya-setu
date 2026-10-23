<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'assigned_mentor_at')) {
                return;
            }

            if (Schema::hasColumn('users', 'assigned_mentor_id')) {
                $table->timestamp('assigned_mentor_at')->nullable()->after('assigned_mentor_id');
            } else {
                $table->timestamp('assigned_mentor_at')->nullable();
            }
        });

        $this->backfillFromAcceptedRequests();
        $this->backfillFromRelated('mentee_enrollments', 'COALESCE(MIN(start_date), MIN(created_at))');
        $this->backfillFromRelated('education_streams', 'MIN(created_at)');
        $this->backfillFromRelated('consultation_sessions', 'MIN(created_at)');

        DB::table('users')
            ->whereNotNull('assigned_mentor_id')
            ->whereNull('assigned_mentor_at')
            ->update(['assigned_mentor_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'assigned_mentor_at')) {
                $table->dropColumn('assigned_mentor_at');
            }
        });
    }

    private function backfillFromAcceptedRequests(): void
    {
        if (! Schema::hasTable('mentor_requests')) {
            return;
        }

        $rows = DB::table('mentor_requests')
            ->select('mentee_id', 'mentor_id', DB::raw('MAX(responded_at) as joined_at'))
            ->where('status', 'accepted')
            ->whereNotNull('responded_at')
            ->groupBy('mentee_id', 'mentor_id')
            ->get();

        $this->applyJoinedDates($rows);
    }

    private function backfillFromRelated(string $table, string $aggregate): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $rows = DB::table($table)
            ->select('mentee_id', 'mentor_id', DB::raw($aggregate.' as joined_at'))
            ->whereNotNull('mentee_id')
            ->whereNotNull('mentor_id')
            ->groupBy('mentee_id', 'mentor_id')
            ->get();

        $this->applyJoinedDates($rows);
    }

    private function applyJoinedDates($rows): void
    {
        foreach ($rows as $row) {
            if (! $row->joined_at || ! $row->mentee_id || ! $row->mentor_id) {
                continue;
            }

            DB::table('users')
                ->where('id', $row->mentee_id)
                ->where('assigned_mentor_id', $row->mentor_id)
                ->whereNull('assigned_mentor_at')
                ->update(['assigned_mentor_at' => $row->joined_at]);
        }
    }
};
