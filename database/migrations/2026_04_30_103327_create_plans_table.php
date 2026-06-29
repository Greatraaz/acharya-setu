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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Basic, Pro, Enterprise
            $table->string('slug')->unique();                // basic, pro, enterprise
            $table->text('description')->nullable();
            $table->string('badge_label')->nullable();       // "Most Popular", "Best Value"
            $table->string('badge_color')->nullable();       // blue, green, orange
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('currency', 10)->default('INR');
            $table->string('trial_days')->nullable();        // 0 = no trial
            $table->json('features')->nullable();            // array of feature strings
            $table->json('limits')->nullable();              // {"users":10,"storage":"5GB","calls":100}
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);  // Highlighted on pricing page
            $table->integer('sort_order')->default(0);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
            $table->string('razorpay_monthly_plan_id')->nullable();
            $table->string('razorpay_yearly_plan_id')->nullable();
            $table->string('color')->nullable();             // hex for UI accent
            $table->string('icon')->nullable();              // icon class or SVG name
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
