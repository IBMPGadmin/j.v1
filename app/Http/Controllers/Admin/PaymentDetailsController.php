<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\User;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentDetailsController extends Controller
{
    /**
     * Display a listing of the payment details.
     */
    public function index(Request $request)
    {
        // Get search parameters
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $package = $request->input('package');

        // Query builder for subscriptions with user and package data
        $query = UserSubscription::with(['user', 'package'])
            ->select('user_subscriptions.*')
            ->join('users', 'user_subscriptions.user_id', '=', 'users.id')
            ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id');

        // Apply search filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('package', function($packageQuery) use ($search) {
                    $packageQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        // Apply date range filters
        if ($startDate) {
            $query->whereDate('user_subscriptions.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('user_subscriptions.created_at', '<=', $endDate);
        }

        // Apply status filter
        if ($status) {
            if ($status === 'active') {
                $query->where('user_subscriptions.status', 'active')
                      ->where('user_subscriptions.expires_at', '>', now());
            } elseif ($status === 'canceled') {
                $query->where('user_subscriptions.status', 'canceled');
            } elseif ($status === 'expired') {
                $query->where('user_subscriptions.expires_at', '<', now())
                      ->where('user_subscriptions.is_trial', false);
            } elseif ($status === 'trial') {
                $query->where('user_subscriptions.is_trial', true);
            }
        }

        // Apply package filter
        if ($package) {
            $query->where('user_subscriptions.subscription_package_id', $package);
        }

        // Get results with pagination
        $subscriptions = $query->orderBy('user_subscriptions.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get all packages for filter dropdown
        $packages = SubscriptionPackage::all();

        // Get payment statistics
        $stats = [
            'total_payments' => UserSubscription::where('payment_status', 'completed')->count(),
            'active_subscriptions' => UserSubscription::where('status', 'active')
                ->where('expires_at', '>', now())
                ->count(),
            'payments_this_month' => UserSubscription::where('payment_status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_revenue' => UserSubscription::where('payment_status', 'completed')
                ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id')
                ->sum('subscription_packages.price'),
        ];

        return view('admin.payments.index', [
            'subscriptions' => $subscriptions,
            'packages' => $packages,
            'stats' => $stats,
        ]);
    }

    /**
     * Generate a PDF report of payment details.
     */
    public function export(Request $request)
    {
        // Get search parameters
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $package = $request->input('package');
        
        // Package name for the report
        $packageName = null;
        if ($package) {
            $packageObj = SubscriptionPackage::find($package);
            $packageName = $packageObj ? $packageObj->name : null;
        }

        // Query builder for subscriptions with user and package data
        $query = UserSubscription::with(['user', 'package'])
            ->select('user_subscriptions.*')
            ->join('users', 'user_subscriptions.user_id', '=', 'users.id')
            ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id');

        // Apply search filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('package', function($packageQuery) use ($search) {
                    $packageQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        // Apply date range filters
        if ($startDate) {
            $query->whereDate('user_subscriptions.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('user_subscriptions.created_at', '<=', $endDate);
        }

        // Apply status filter
        if ($status) {
            if ($status === 'active') {
                $query->where('user_subscriptions.status', 'active')
                      ->where('user_subscriptions.expires_at', '>', now());
            } elseif ($status === 'canceled') {
                $query->where('user_subscriptions.status', 'canceled');
            } elseif ($status === 'expired') {
                $query->where('user_subscriptions.expires_at', '<', now())
                      ->where('user_subscriptions.is_trial', false);
            } elseif ($status === 'trial') {
                $query->where('user_subscriptions.is_trial', true);
            }
        }

        // Apply package filter
        if ($package) {
            $query->where('user_subscriptions.subscription_package_id', $package);
        }

        // Get all results for PDF (no pagination)
        $subscriptions = $query->orderBy('user_subscriptions.created_at', 'desc')->get();

        // Get payment statistics
        $stats = [
            'total_payments' => UserSubscription::where('payment_status', 'completed')->count(),
            'active_subscriptions' => UserSubscription::where('status', 'active')
                ->where('expires_at', '>', now())
                ->count(),
            'payments_this_month' => UserSubscription::where('payment_status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_revenue' => UserSubscription::where('payment_status', 'completed')
                ->join('subscription_packages', 'user_subscriptions.subscription_package_id', '=', 'subscription_packages.id')
                ->sum('subscription_packages.price'),
        ];

        // Generate PDF with data
        $pdf = PDF::loadView('admin.payments.pdf_report', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'status' => $status,
            'packageName' => $packageName,
        ]);

        // Download PDF
        return $pdf->download('payment_report_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Display details for a specific subscription.
     */
    public function show($id)
    {
        $subscription = UserSubscription::with(['user', 'package'])->findOrFail($id);
        
        // Get all subscriptions for this user for history
        $userSubscriptions = collect([]);
        $userSubscriptionsCount = 0;
        
        if ($subscription->user) {
            $userSubscriptions = UserSubscription::with(['package'])
                ->where('user_id', $subscription->user_id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            $userSubscriptionsCount = $userSubscriptions->count();
        }
        
        return view('admin.payments.view', [
            'subscription' => $subscription,
            'userSubscriptions' => $userSubscriptions,
            'userSubscriptionsCount' => $userSubscriptionsCount
        ]);
    }
}
