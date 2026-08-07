<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (! Schema::hasColumn('plans', 'badge_label')) {
                $table->string('badge_label')->nullable();
            }
            if (! Schema::hasColumn('plans', 'badge_color')) {
                $table->string('badge_color')->nullable();
            }
            if (! Schema::hasColumn('plans', 'price_monthly')) {
                $table->decimal('price_monthly', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('plans', 'price_yearly')) {
                $table->decimal('price_yearly', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('plans', 'currency')) {
                $table->string('currency', 10)->default('INR');
            }
            if (! Schema::hasColumn('plans', 'trial_days')) {
                $table->unsignedInteger('trial_days')->nullable()->default(0);
            }
            if (! Schema::hasColumn('plans', 'limits')) {
                $table->json('limits')->nullable();
            }
            if (! Schema::hasColumn('plans', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('plans', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (! Schema::hasColumn('plans', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (! Schema::hasColumn('plans', 'color')) {
                $table->string('color')->nullable();
            }
            if (! Schema::hasColumn('plans', 'icon')) {
                $table->string('icon')->nullable();
            }
            if (! Schema::hasColumn('plans', 'stripe_monthly_price_id')) {
                $table->string('stripe_monthly_price_id')->nullable();
            }
            if (! Schema::hasColumn('plans', 'stripe_yearly_price_id')) {
                $table->string('stripe_yearly_price_id')->nullable();
            }
            if (! Schema::hasColumn('plans', 'razorpay_monthly_plan_id')) {
                $table->string('razorpay_monthly_plan_id')->nullable();
            }
            if (! Schema::hasColumn('plans', 'razorpay_yearly_plan_id')) {
                $table->string('razorpay_yearly_plan_id')->nullable();
            }
        });

        // Backfill from legacy columns used by the API.
        if (Schema::hasColumn('plans', 'name') && Schema::hasColumn('plans', 'plan_name')) {
            DB::statement('UPDATE plans SET name = plan_name WHERE (name IS NULL OR name = "") AND plan_name IS NOT NULL');
            DB::statement('UPDATE plans SET plan_name = name WHERE (plan_name IS NULL OR plan_name = "") AND name IS NOT NULL');
        }

        if (Schema::hasColumn('plans', 'price_monthly') && Schema::hasColumn('plans', 'price')) {
            DB::statement('UPDATE plans SET price_monthly = price WHERE price_monthly = 0 AND price IS NOT NULL AND price > 0');
            DB::statement('UPDATE plans SET price = price_monthly WHERE (price IS NULL OR price = 0) AND price_monthly IS NOT NULL');
        }

        if (Schema::hasColumn('plans', 'is_active') && Schema::hasColumn('plans', 'status')) {
            DB::statement("UPDATE plans SET is_active = 1 WHERE status = 'active'");
            DB::statement("UPDATE plans SET is_active = 0 WHERE status IS NOT NULL AND status <> 'active'");
            DB::statement("UPDATE plans SET status = 'active' WHERE is_active = 1 AND (status IS NULL OR status = '')");
            DB::statement("UPDATE plans SET status = 'inactive' WHERE is_active = 0 AND (status IS NULL OR status = '' OR status = 'active')");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            $columns = [
                'name',
                'badge_label',
                'badge_color',
                'price_monthly',
                'price_yearly',
                'currency',
                'trial_days',
                'limits',
                'is_active',
                'is_featured',
                'sort_order',
                'color',
                'icon',
                'stripe_monthly_price_id',
                'stripe_yearly_price_id',
                'razorpay_monthly_plan_id',
                'razorpay_yearly_plan_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
