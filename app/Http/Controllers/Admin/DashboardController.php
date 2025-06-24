<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\SubscriptionPackage;
use App\Models\LegalDocument;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with summary data.
     */    public function index()
    {
        // Current year 
        $currentYear = date('Y');
        
        // User statistics
        $totalUsers = User::count();
        $activeUsers = User::where('status', 1)->orWhereNull('status')->count();
        $inactiveUsers = User::where('status', 0)->count();
        $adminUsers = User::where('role', 'admin')->count();
        $regularUsers = User::where('role', 'user')->orWhereNull('role')->count();
        
        // Get new users count by month for the current year
        $usersThisYear = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
            
        // If no data exists for the current year, generate sample data for demonstration
        if (empty($usersThisYear)) {
            $usersThisYear = $this->generateSampleMonthlyData(5, 15);
        }
        
        $userMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $userMonthlyData[] = $usersThisYear[$i] ?? 0;
        }
        
        // Subscription statistics
        $totalSubscriptions = UserSubscription::count();
        $activeSubscriptions = UserSubscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();
        $trialSubscriptions = UserSubscription::where('status', 'trial')
            ->where('trial_ends_at', '>', now())
            ->count();
        $expiredSubscriptions = UserSubscription::where(function($query) {
            $query->where('status', 'expired')
                ->orWhere(function($q) {
                    $q->where('expires_at', '<', now())
                      ->where('status', 'active');
                });
        })->count();
        $canceledSubscriptions = UserSubscription::where('status', 'canceled')
            ->orWhere('status', 'cancelled')
            ->count();
            
        // Get subscription data by month for the current year
        $subscriptionsThisYear = UserSubscription::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
            
        $subscriptionMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $subscriptionMonthlyData[] = $subscriptionsThisYear[$i] ?? 0;
        }
        
        // Payment statistics
        $totalRevenue = UserSubscription::where('payment_status', 'completed')
            ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id')
            ->sum('subscription_packages.price');
            
        $revenueThisMonth = UserSubscription::where('payment_status', 'completed')
            ->whereMonth('user_subscriptions.created_at', date('m'))
            ->whereYear('user_subscriptions.created_at', date('Y'))
            ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id')
            ->sum('subscription_packages.price');
            
        $revenueLastMonth = UserSubscription::where('payment_status', 'completed')
            ->whereMonth('user_subscriptions.created_at', date('m', strtotime('-1 month')))
            ->whereYear('user_subscriptions.created_at', date('Y', strtotime('-1 month')))
            ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id')
            ->sum('subscription_packages.price');
              // Get revenue data by month for the current year
        $revenueThisYear = UserSubscription::selectRaw('MONTH(user_subscriptions.created_at) as month, SUM(subscription_packages.price) as total')
            ->where('payment_status', 'completed')
            ->whereYear('user_subscriptions.created_at', $currentYear)
            ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
            
        // If no data exists for the current year, generate sample data for demonstration
        if (empty($revenueThisYear)) {
            $revenueThisYear = $this->generateSampleMonthlyData(500, 2000);
        }
            
        $revenueMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueMonthlyData[] = $revenueThisYear[$i] ?? 0;
        }
        
        // Package statistics
        $subscriptionPackages = SubscriptionPackage::withCount(['subscriptions' => function($query) {
            $query->where('status', 'active');
        }])
        ->get();
        
        // Recent users
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Recent subscriptions
        $recentSubscriptions = UserSubscription::with(['user', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
              // Get subscription count by package
        $packageData = SubscriptionPackage::withCount('subscriptions')
            ->get();
            
        $packageLabels = $packageData->pluck('name')->toArray();
        $packageValues = $packageData->pluck('subscriptions_count')->toArray();
        
        // If no packages or no subscriptions, generate sample data
        if (empty($packageLabels) || (count(array_filter($packageValues)) === 0)) {
            // Create sample package data if none exists
            if (empty($packageLabels)) {
                $packageLabels = ['Basic Plan', 'Premium Plan', 'Business Plan'];
            }
            
            // Generate random values if all values are 0
            if (count(array_filter($packageValues)) === 0) {
                $packageValues = [rand(5, 20), rand(10, 30), rand(3, 15)];
            }
        }
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'adminUsers',
            'regularUsers',
            'userMonthlyData',
            'totalSubscriptions',
            'activeSubscriptions',
            'trialSubscriptions',
            'expiredSubscriptions',
            'canceledSubscriptions',
            'subscriptionMonthlyData',
            'totalRevenue',
            'revenueThisMonth',
            'revenueLastMonth',
            'revenueMonthlyData',
            'subscriptionPackages',
            'recentUsers',
            'recentSubscriptions',
            'packageLabels',
            'packageValues'
        ));
    }

    /**
     * Generate sample monthly data for demonstration purposes
     */
    private function generateSampleMonthlyData($min = 5, $max = 20)
    {
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[$i] = rand($min, $max);
        }
        return $data;
    }
}
