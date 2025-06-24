<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SubscriptionPackage;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DashboardDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data if needed
        // Uncomment if you want to clear data before seeding
        // DB::table('user_subscriptions')->truncate();
        // DB::table('subscription_packages')->truncate();
        // User::where('role', '!=', 'admin')->delete();
        
        // Create subscription packages if they don't exist
        $this->createSubscriptionPackages();
        
        // Create users with a nice distribution pattern
        $this->createUsers();
        
        // Create subscriptions with a realistic distribution
        $this->createSubscriptions();
        
        // Set some users as inactive to make the dashboard more realistic
        $this->setInactiveUsers();
        
        // Update some stats to make nice-looking charts
        $this->adjustDataForNicerCharts();
    }
    
    /**
     * Create subscription packages
     */
    private function createSubscriptionPackages()
    {
        if (SubscriptionPackage::count() === 0) {
            $packages = [
                [
                    'name' => 'Basic Plan',
                    'type' => 'monthly',
                    'price' => 29.99,
                    'duration_days' => 30,
                    'description' => 'Basic features for individual users',
                    'features' => json_encode(['Basic support', '5 document uploads', 'Basic document search']),
                    'is_active' => true,
                ],
                [
                    'name' => 'Premium Plan',
                    'type' => 'monthly',
                    'price' => 49.99,
                    'duration_days' => 30,
                    'description' => 'Premium features for professional users',
                    'features' => json_encode(['Priority support', '20 document uploads', 'Advanced search', 'Document analytics']),
                    'is_active' => true,
                ],
                [
                    'name' => 'Business Plan',
                    'type' => 'yearly',
                    'price' => 499.99,
                    'duration_days' => 365,
                    'description' => 'Enterprise features for business users',
                    'features' => json_encode(['24/7 Priority support', 'Unlimited document uploads', 'Advanced analytics', 'API access', 'Team collaboration']),
                    'is_active' => true,
                ],
                [
                    'name' => 'Student Plan',
                    'type' => 'monthly',
                    'price' => 19.99,
                    'duration_days' => 30,
                    'description' => 'Affordable plan for students',
                    'features' => json_encode(['Email support', '10 document uploads', 'Basic document search', 'Student resources']),
                    'is_active' => true,
                ],
            ];

            foreach ($packages as $package) {
                SubscriptionPackage::create($package);
            }
        }
    }
    
    /**
     * Create users with realistic monthly distribution
     */
    private function createUsers()
    {
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
        
        // Only create users if we have fewer than expected
        if (User::count() < 50) {
            // Realistic growth pattern for the year
            $monthlyDistribution = [
                1 => 8,   // January
                2 => 10,  // February
                3 => 15,  // March
                4 => 18,  // April
                5 => 22,  // May
                6 => 25,  // June
                7 => 20,  // July (projected)
                8 => 18,  // August (projected)
                9 => 16,  // September (projected)
                10 => 20, // October (projected)
                11 => 25, // November (projected)
                12 => 30, // December (projected)
            ];
            
            $currentYear = date('Y');
            
            foreach ($monthlyDistribution as $month => $count) {
                // Only create users for months up to current month
                if ($month <= date('n')) {
                    for ($i = 1; $i <= $count; $i++) {
                        $name = $this->getRandomName();
                        $email = strtolower(str_replace(' ', '.', $name)) . '.' . rand(100, 999) . '@example.com';
                        
                        // Skip if email already exists
                        if (User::where('email', $email)->exists()) {
                            continue;
                        }
                        
                        $day = rand(1, min(28, Carbon::createFromDate($currentYear, $month, 1)->daysInMonth));
                        
                        User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => Hash::make('password'),
                            'role' => 'user',
                            'status' => 1, // Active by default
                            'created_at' => Carbon::createFromDate($currentYear, $month, $day),
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Create subscriptions with realistic distribution
     */
    private function createSubscriptions()
    {
        $users = User::where('role', 'user')->get();
        $packages = SubscriptionPackage::all();
        
        if ($packages->isEmpty()) {
            return;
        }
        
        // If we already have a good number of subscriptions, skip
        if (UserSubscription::count() > 100) {
            return;
        }
        
        // Package distribution (percentages)
        $packageDistribution = [
            'Basic Plan' => 45,
            'Premium Plan' => 30,
            'Business Plan' => 15,
            'Student Plan' => 10,
        ];
        
        // Status distribution (percentages)
        $statusDistribution = [
            'active' => 70,
            'trial' => 10,
            'expired' => 15,
            'canceled' => 5,
        ];
        
        foreach ($users as $user) {
            // Determine if user has subscription based on creation date
            // Newer users less likely to have subscribed yet
            $monthsAgo = Carbon::now()->diffInMonths($user->created_at);
            $subscriptionProbability = min(90, 50 + ($monthsAgo * 10)); // Percentage chance
            
            if (rand(1, 100) <= $subscriptionProbability) {
                // Determine package
                $packageName = $this->getRandomWeighted($packageDistribution);
                $package = $packages->where('name', $packageName)->first();
                
                if (!$package) {
                    $package = $packages->first();
                }
                
                // Determine status
                $status = $this->getRandomWeighted($statusDistribution);
                
                // Create subscription date (some time after user creation)
                $subscriptionDate = Carbon::parse($user->created_at)->addDays(rand(1, 14));
                
                // Calculate expiry based on package duration
                $expiresAt = Carbon::parse($subscriptionDate)->addDays($package->duration_days);
                
                // Determine payment status based on subscription status
                $paymentStatus = ($status == 'active' || $status == 'expired') ? 'completed' : 'pending';
                
                // Create subscription
                UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_package_id' => $package->id,
                    'starts_at' => $subscriptionDate,
                    'expires_at' => $expiresAt,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'reference' => 'REF-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'created_at' => $subscriptionDate,
                    'updated_at' => $subscriptionDate,
                ]);
            }
        }
    }
    
    /**
     * Set some users as inactive
     */
    private function setInactiveUsers()
    {
        // Make ~15% of users inactive
        $users = User::where('role', 'user')->get();
        $inactiveCount = ceil($users->count() * 0.15);
        
        $users->random($inactiveCount)->each(function ($user) {
            $user->status = 0;
            $user->save();
        });
    }
    
    /**
     * Adjust data to make nicer-looking charts
     */
    private function adjustDataForNicerCharts()
    {
        // Create a realistic growth pattern for revenue
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Trend data - increasing pattern with seasonal variations
        $revenueMultipliers = [
            1 => 0.8,  // January
            2 => 0.9,  // February
            3 => 1.0,  // March
            4 => 1.1,  // April
            5 => 1.2,  // May
            6 => 1.3,  // June
            7 => 1.3,  // July
            8 => 1.2,  // August
            9 => 1.4,  // September
            10 => 1.5, // October
            11 => 1.7, // November
            12 => 1.9, // December
        ];
        
        // Base monthly revenue
        $baseRevenue = 1500;
        
        // Update subscription creation dates to match revenue pattern
        for ($month = 1; $month <= $currentMonth; $month++) {
            // Skip adjustment if we're in the first month of the year
            if ($month == 1 && $currentMonth > 2) {
                continue;
            }
            
            $targetRevenue = $baseRevenue * $revenueMultipliers[$month];
            
            // Get subscriptions for this month
            $subscriptions = UserSubscription::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->with('package')
                ->get();
            
            // Calculate current revenue for the month
            $currentRevenue = $subscriptions->sum(function ($sub) {
                return $sub->package ? $sub->package->price : 0;
            });
            
            // If we need to adjust
            if ($currentRevenue < $targetRevenue && $subscriptions->count() > 0) {
                // Add more subscriptions to reach target revenue
                $packageIds = SubscriptionPackage::pluck('id')->toArray();
                $diff = $targetRevenue - $currentRevenue;
                
                while ($diff > 0 && !empty($packageIds)) {
                    // Get a random package
                    $packageId = $packageIds[array_rand($packageIds)];
                    $package = SubscriptionPackage::find($packageId);
                    
                    if (!$package) continue;
                    
                    // Find a user without subscription in this month
                    $user = User::whereDoesntHave('subscriptions', function ($query) use ($month, $currentYear) {
                        $query->whereYear('created_at', $currentYear)
                            ->whereMonth('created_at', $month);
                    })->where('role', 'user')
                      ->inRandomOrder()
                      ->first();
                    
                    if (!$user) {
                        // If no users without subscriptions, create a new user
                        $name = $this->getRandomName();
                        $email = strtolower(str_replace(' ', '.', $name)) . '.' . rand(100, 999) . '@example.com';
                        
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => Hash::make('password'),
                            'role' => 'user',
                            'status' => 1,
                            'created_at' => Carbon::createFromDate($currentYear, $month, rand(1, 28)),
                        ]);
                    }
                    
                    // Create subscription
                    $day = rand(1, min(28, Carbon::createFromDate($currentYear, $month, 1)->daysInMonth));
                    $date = Carbon::createFromDate($currentYear, $month, $day);
                    
                    UserSubscription::create([
                        'user_id' => $user->id,
                        'subscription_package_id' => $package->id,
                        'starts_at' => $date,
                        'expires_at' => Carbon::parse($date)->addDays($package->duration_days),
                        'status' => 'active',
                        'payment_status' => 'completed',
                        'reference' => 'REF-' . strtoupper(substr(md5(rand()), 0, 8)),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    
                    $diff -= $package->price;
                }
            }
        }
    }
    
    /**
     * Get a random weighted item based on distribution percentages
     */
    private function getRandomWeighted(array $distribution)
    {
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($distribution as $item => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $item;
            }
        }
        
        // Default to first item if something goes wrong
        return array_key_first($distribution);
    }
    
    /**
     * Get a random realistic name
     */
    private function getRandomName()
    {
        $firstNames = ['James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Joseph', 'Thomas', 'Charles', 
                      'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen',
                      'Christopher', 'Daniel', 'Matthew', 'Anthony', 'Mark', 'Donald', 'Steven', 'Paul', 'Andrew', 'Joshua',
                      'Michelle', 'Amanda', 'Kimberly', 'Melissa', 'Stephanie', 'Rebecca', 'Laura', 'Emily', 'Megan', 'Hannah'];
        
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
                     'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
                     'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
                     'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
}
