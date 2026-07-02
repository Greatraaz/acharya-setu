<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_sessions', 'agenda')) {
                $table->text('agenda')->nullable()->after('title');
            }

            if (! Schema::hasColumn('consultation_sessions', 'meeting_provider')) {
                $table->string('meeting_provider')->nullable()->after('meeting_link');
            }

            if (! Schema::hasColumn('consultation_sessions', 'meeting_channel') && Schema::hasColumn('consultation_sessions', 'channel')) {
                $table->renameColumn('channel', 'meeting_channel');
            } elseif (! Schema::hasColumn('consultation_sessions', 'meeting_channel')) {
                $table->string('meeting_channel')->nullable()->after('meeting_link');
            }

            if (! Schema::hasColumn('consultation_sessions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }

            if (! Schema::hasColumn('consultation_sessions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('cancelled_at');
            }

            if (! Schema::hasColumn('consultation_sessions', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }

            if (! Schema::hasColumn('consultation_sessions', 'actual_duration_seconds')) {
                $table->unsignedInteger('actual_duration_seconds')->nullable()->after('ended_at');
            }

            if (! Schema::hasColumn('consultation_sessions', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('currency');
            }

            if (! Schema::hasColumn('consultation_sessions', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('consultation_sessions', 'booking_ref') && Schema::hasColumn('consultation_sessions', 'booking_id')) {
                $table->renameColumn('booking_id', 'booking_ref');
            } elseif (! Schema::hasColumn('consultation_sessions', 'booking_ref')) {
                $table->string('booking_ref')->nullable()->unique()->after('payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            $columns = [
                'agenda',
                'meeting_provider',
                'cancelled_at',
                'started_at',
                'ended_at',
                'actual_duration_seconds',
                'payment_status',
                'payment_reference',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('consultation_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('consultation_sessions', 'meeting_channel') && ! Schema::hasColumn('consultation_sessions', 'channel')) {
                $table->renameColumn('meeting_channel', 'channel');
            }

            if (Schema::hasColumn('consultation_sessions', 'booking_ref') && ! Schema::hasColumn('consultation_sessions', 'booking_id')) {
                $table->renameColumn('booking_ref', 'booking_id');
            }
        });
    }
};
