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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
 
        // Seed default values
        $defaults = [
            'app_name'              => config('app.name', 'MyApp'),
            'app_url'               => config('app.url'),
            'default_language'      => 'en',
            'timezone'              => 'Asia/Kolkata',
            'date_format'           => 'd/m/Y',
            'default_currency'      => 'INR',
            'currency_symbol'       => '₹',
            'currency_position'     => 'before',
            'storage_driver'        => 'local',
            'video_provider'        => 'agora',
            'sms_active_provider'   => 'msg91',
            'maintenance_mode'      => '0',
            'user_registration'     => '1',
            'email_verification'    => '1',
            'two_factor_auth'       => '0',
            'razorpay_mode'         => 'test',
            'stripe_mode'           => 'test',
            'paypal_mode'           => 'sandbox',
            'msg91_route'           => '4',
            'fast2sms_route'        => 'dlt_manual',
            'sns_region'            => 'ap-south-1',
            'agora_token_expiry'    => '3600',
            'video_default_duration'  => '60',
            'video_max_participants'  => '10',
            'video_recording_enabled' => '1',
            'video_waiting_room'      => '1',
            'video_screen_sharing'    => '1',
            'google_auto_calendar'    => '1',
            'google_send_invites'     => '1',
            'google_sync_cancellations' => '1',
            'notify_new_user'         => '1',
            'notify_booking'          => '1',
            'notify_payment'          => '1',
            'notify_failed_login'     => '0',
            'notify_cancellation'     => '1',
        ];
 
        foreach ($defaults as $key => $value) {
            \DB::table('app_settings')->insertOrIgnore(['key' => $key, 'value' => $value]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
