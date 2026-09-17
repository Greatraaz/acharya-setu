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

        if (Schema::hasColumn('plans', 'name') && Schema::hasColumn('plans', 'plan_name')) {
            DB::statement("UPDATE plans SET name = plan_name WHERE (name IS NULL OR name = '') AND plan_name IS NOT NULL AND plan_name <> ''");
        }

        if (Schema::hasColumn('plans', 'price_monthly') && Schema::hasColumn('plans', 'price')) {
            DB::statement('UPDATE plans SET price_monthly = price WHERE (price_monthly IS NULL OR price_monthly = 0) AND price IS NOT NULL AND price > 0');
        }

        if (Schema::hasColumn('plans', 'is_active') && Schema::hasColumn('plans', 'status')) {
            DB::statement("UPDATE plans SET is_active = 1 WHERE status = 'active'");
            DB::statement("UPDATE plans SET is_active = 0 WHERE status IS NOT NULL AND status <> 'active'");
        }

        $drop = array_values(array_filter([
            'plan_name',
            'price',
            'status',
            'level',
            'features',
            'icon',
            'stripe_monthly_price_id',
            'stripe_yearly_price_id',
            'razorpay_monthly_plan_id',
            'razorpay_yearly_plan_id',
        ], fn (string $column) => Schema::hasColumn('plans', $column)));

        if ($drop === []) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'plan_name')) {
                $table->string('plan_name')->nullable();
            }
            if (! Schema::hasColumn('plans', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('plans', 'status')) {
                $table->string('status', 20)->nullable();
            }
            if (! Schema::hasColumn('plans', 'level')) {
                $table->string('level', 50)->nullable();
            }
            if (! Schema::hasColumn('plans', 'features')) {
                $table->json('features')->nullable();
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

        if (Schema::hasColumn('plans', 'plan_name') && Schema::hasColumn('plans', 'name')) {
            DB::statement('UPDATE plans SET plan_name = name WHERE plan_name IS NULL OR plan_name = \'\'');
        }

        if (Schema::hasColumn('plans', 'price') && Schema::hasColumn('plans', 'price_monthly')) {
            DB::statement('UPDATE plans SET price = price_monthly WHERE price IS NULL OR price = 0');
        }

        if (Schema::hasColumn('plans', 'status') && Schema::hasColumn('plans', 'is_active')) {
            DB::statement("UPDATE plans SET status = CASE WHEN is_active = 1 THEN 'active' ELSE 'inactive' END");
        }
    }
};
