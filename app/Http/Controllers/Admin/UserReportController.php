<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class UserReportController extends Controller
{
    /**
     * Display the users report page.
     */
    public function index(Request $request)
    {
        // Get search parameters
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $role = $request->input('role');
        $status = $request->input('status');

        // Query builder for users with their subscription data
        $query = User::query()->with(['subscriptions' => function($q) {
            $q->with('package')->orderBy('created_at', 'desc');
        }]);

        // Apply search filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply date range filters
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply role filter
        if ($role) {
            $query->where('role', $role);
        }

        // Apply status filter
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // Get results with pagination
        $users = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get user statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 1)->count(),
            'inactive_users' => User::where('status', 0)->orWhereNull('status')->count(),
            'users_with_subscriptions' => User::whereHas('subscriptions')->count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'user_count' => User::where('role', 'user')->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.reports.users', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Generate a PDF report of users.
     */
    public function export(Request $request)
    {
        // Get search parameters
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $role = $request->input('role');
        $status = $request->input('status');

        // Query builder for users with their subscription data
        $query = User::query()->with(['subscriptions' => function($q) {
            $q->with('package')->orderBy('created_at', 'desc');
        }]);

        // Apply search filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply date range filters
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply role filter
        if ($role) {
            $query->where('role', $role);
        }

        // Apply status filter
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // Get all results for PDF (no pagination)
        $users = $query->orderBy('created_at', 'desc')->get();

        // Get user statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 1)->count(),
            'inactive_users' => User::where('status', 0)->orWhereNull('status')->count(),
            'users_with_subscriptions' => User::whereHas('subscriptions')->count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'user_count' => User::where('role', 'user')->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Generate PDF with data
        $pdf = PDF::loadView('admin.reports.users_pdf', [
            'users' => $users,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'role' => $role,
            'status' => $status,
        ]);

        // Download PDF
        return $pdf->download('users_report_' . now()->format('Y-m-d_His') . '.pdf');
    }
}
