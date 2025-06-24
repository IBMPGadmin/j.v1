<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPackage;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Single User Lifetime',
                'type' => 'lifetime',
                'price' => 2000.00,
                'description' => 'Lifetime access for a single user',
                'features' => [
                    'Built In Issue reporting system',
                    'Onboarding & Training Included',
                    '1 user license'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Enterprise Lifetime',
                'type' => 'lifetime',
                'price' => 5000.00,
                'description' => 'Lifetime access for enterprise users',
                'features' => [
                    'Built In Issue reporting system',
                    'Premium Support',
                    'White-label Branding',
                    'Onboarding & Training Included',
                    'Multi-user license'
                ],
                'is_active' => true
            ],
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::create($package);
        }
    }
}
