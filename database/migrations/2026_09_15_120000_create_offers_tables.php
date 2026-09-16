<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('audience', 30); // new_joinee | selected_mentees
            $table->decimal('amount', 10, 2);
            $table->string('coupon_code', 40)->nullable()->unique();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('min_session_amount', 10, 2)->nullable();
            $table->date('starts_at');
            $table->date('expires_at');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('offer_mentee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['offer_id', 'mentee_id']);
        });

        Schema::create('offer_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('consultation_session_id')->nullable()->constrained('consultation_sessions')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('type', 30); // wallet_credit | session_discount
            $table->timestamps();

            $table->index(['offer_id', 'user_id']);
        });

        if (Schema::hasTable('consultation_sessions')) {
            Schema::table('consultation_sessions', function (Blueprint $table) {
                if (! Schema::hasColumn('consultation_sessions', 'offer_id')) {
                    $table->foreignId('offer_id')->nullable()->after('amount')->constrained('offers')->nullOnDelete();
                }
                if (! Schema::hasColumn('consultation_sessions', 'coupon_discount')) {
                    $table->decimal('coupon_discount', 10, 2)->default(0)->after('offer_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('consultation_sessions')) {
            Schema::table('consultation_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('consultation_sessions', 'coupon_discount')) {
                    $table->dropColumn('coupon_discount');
                }
                if (Schema::hasColumn('consultation_sessions', 'offer_id')) {
                    $table->dropConstrainedForeignId('offer_id');
                }
            });
        }

        Schema::dropIfExists('offer_redemptions');
        Schema::dropIfExists('offer_mentee');
        Schema::dropIfExists('offers');
    }
};
