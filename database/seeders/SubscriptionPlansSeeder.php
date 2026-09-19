<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Plan::starterCatalog() as $row) {
            $plan = Plan::withTrashed()->where('slug', $row['slug'])->first();

            $payload = [
                'name'                    => $row['name'],
                'description'             => $row['description'],
                'price_monthly'           => $row['price_monthly'],
                'price_yearly'            => $row['price_yearly'],
                'currency'                => 'INR',
                'duration'                => 30,
                'sort_order'              => $row['sort_order'],
                'badge_label'             => $row['badge_label'],
                'badge_color'             => $row['badge_color'],
                'is_featured'             => $row['is_featured'],
                'is_active'               => true,
                'color'                   => $row['color'],
                'limits'                  => [
                    'sessions' => null,
                    'free_session_minutes' => $row['free_session_minutes'],
                    'free_session_max_duration' => $row['free_session_max_duration'],
                ],
                'progress_report_enabled' => true,
                'benefits'                => $row['benefits'],
            ];

            if ($plan) {
                // Keep whatever display name admin already set; only backfill empty benefits.
                unset($payload['name']);
                if (empty($plan->benefits)) {
                    $plan->update($payload);
                } else {
                    // Always refresh free-minute entitlements from catalog.
                    $plan->update(['limits' => $payload['limits']]);
                }

                continue;
            }

            $payload['slug'] = $row['slug'];
            Plan::create($payload);
        }
    }
}
