<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SubscriptionPackage;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create subscription packages if they don't exist
        if (SubscriptionPackage::count() === 0) {
            $packages = [
                [
                    'name' => 'Basic Plan',
                    'type' => 'monthly',
                    'price' => 29.99,
                    'duration_days' => 30,
                    'description' => 'Basic features for individual users',
                    'features' => json_encode(['Basic support', '5 document uploads', 'Limited access']),
                    'is_active' => true,
                ],
                [
                    'name' => 'Premium Plan',
                    'type' => 'monthly',
                    'price' => 49.99,
                    'duration_days' => 30,
                    'description' => 'Premium features for professional users',
                    'features' => json_encode(['Premium support', '20 document uploads', 'Full access']),
                    'is_active' => true,
                ],
                [
                    'name' => 'Business Plan',
                    'type' => 'yearly',
                    'price' => 499.99,
                    'duration_days' => 365,
                    'description' => 'Enterprise features for business users',
                    'features' => json_encode(['Priority support', 'Unlimited document uploads', 'Full access', 'API access']),
                    'is_active' => true,
                ],
            ];

            foreach ($packages as $package) {
                SubscriptionPackage::create($package);
            }
        }

        // Create users if needed
        if (User::count() < 5) {
            // Create admin if doesn't exist
            if (!User::where('email', 'admin@jurislocator.com')->exists()) {
                User::create([
                    'name' => 'Admin User',
                    'email' => 'admin@jurislocator.com',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'created_at' => Carbon::now()->subMonths(6),
                ]);
            }

            // Create some regular users for different months
            $months = [1, 2, 3, 4, 5, 6];
            foreach ($months as $month) {
                for ($i = 1; $i <= rand(1, 5); $i++) {
                    $user = User::create([
                        'name' => "User {$month}-{$i}",
                        'email' => "user_{$month}_{$i}@example.com",
                        'password' => Hash::make('password'),
                        'role' => 'user',
                        'status' => rand(0, 1),
                        'created_at' => Carbon::now()->setMonth($month)->setDay(rand(1, 28)),
                    ]);
                    
                    // Add a subscription for the user
                    $packageId = rand(1, 3);
                    $subscriptionStatus = ['active', 'trial', 'expired', 'canceled'][rand(0, 3)];
                    
                    UserSubscription::create([
                        'user_id' => $user->id,
                        'subscription_package_id' => $packageId,
                        'starts_at' => Carbon::now()->setMonth($month)->setDay(rand(1, 28)),
                        'expires_at' => Carbon::now()->setMonth($month)->setDay(rand(1, 28))->addDays(30),
                        'status' => $subscriptionStatus,
                        'payment_status' => $subscriptionStatus === 'active' ? 'completed' : 'pending',
                        'created_at' => Carbon::now()->setMonth($month)->setDay(rand(1, 28)),
                    ]);
                }
            }
        }
    }
}
