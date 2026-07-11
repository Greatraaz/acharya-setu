<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('consultation_sessions', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->after('payment_reference')->index();
            }
            if (! Schema::hasColumn('consultation_sessions', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('consultation_sessions', 'razorpay_payment_id')) {
                $table->dropColumn('razorpay_payment_id');
            }
            if (Schema::hasColumn('consultation_sessions', 'razorpay_order_id')) {
                $table->dropColumn('razorpay_order_id');
            }
        });
    }
};
