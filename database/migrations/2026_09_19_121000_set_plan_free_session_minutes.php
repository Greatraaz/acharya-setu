<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans') || ! Schema::hasColumn('plans', 'limits')) {
            return;
        }

        $defaults = [
            'essential' => ['free_session_minutes' => 30, 'free_session_max_duration' => 30],
            'growth'    => ['free_session_minutes' => 60, 'free_session_max_duration' => 60],
            'premium'   => ['free_session_minutes' => 60, 'free_session_max_duration' => 60],
        ];

        foreach ($defaults as $slug => $entitlement) {
            $row = DB::table('plans')->where('slug', $slug)->first();
            if (! $row) {
                continue;
            }

            $limits = [];
            if (! empty($row->limits)) {
                $decoded = json_decode($row->limits, true);
                $limits = is_array($decoded) ? $decoded : [];
            }

            $limits['sessions'] = $limits['sessions'] ?? null;
            $limits['free_session_minutes'] = $entitlement['free_session_minutes'];
            $limits['free_session_max_duration'] = $entitlement['free_session_max_duration'];

            DB::table('plans')->where('id', $row->id)->update([
                'limits'     => json_encode($limits),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Keep entitlement keys; no-op.
    }
};
