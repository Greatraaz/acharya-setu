<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Paid / ready sessions that used legacy statuses → upcoming
        DB::table('consultation_sessions')
            ->whereIn('status', ['pending', 'confirmed', 'ongoing'])
            ->where(function ($q) {
                $q->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'pending');
            })
            ->update(['status' => 'upcoming']);

        // Unpaid checkout holds → cancel (no longer keep pending rows)
        DB::table('consultation_sessions')
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->update([
                'status'              => 'cancelled',
                'cancellation_reason' => 'Legacy unpaid checkout cleared during status normalization',
                'cancelled_at'        => now(),
            ]);

        // No-shows and any leftover ongoing → completed
        DB::table('consultation_sessions')
            ->whereIn('status', ['no_show', 'ongoing'])
            ->update(['status' => 'completed']);

        // Past upcoming sessions whose end time already passed → completed
        DB::table('consultation_sessions')
            ->where('status', 'upcoming')
            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_minutes, 0) MINUTE) < ?', [
                now()->timezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
            ])
            ->update(['status' => 'completed']);
    }

    public function down(): void
    {
        // Irreversible data normalization
    }
};
