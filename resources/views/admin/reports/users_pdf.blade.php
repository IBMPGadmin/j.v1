<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #4680ff;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            justify-content: space-between;
        }
        .stat-box {
            width: 23%;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            margin-bottom: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            display: inline-block;
            vertical-align: top;
        }
        .stat-box h3 {
            margin: 0;
            font-size: 20px;
            color: #4680ff;
        }
        .stat-box p {
            margin: 5px 0 0;
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
            font-size: 12px;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-secondary { background-color: #6c757d; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>JurisLocator - Users Report</h1>
        <p>Generated on: {{ now()->format('F d, Y, h:i A') }}</p>
        @if($startDate || $endDate || $search || $role || $status !== null)
            <div class="filters">
                <strong>Filters:</strong>
                @if($search)
                    Search: "{{ $search }}" |
                @endif
                @if($startDate)
                    From: {{ $startDate }} |
                @endif
                @if($endDate)
                    To: {{ $endDate }} |
                @endif
                @if($role)
                    Role: {{ ucfirst($role) }} |
                @endif
                @if($status !== null && $status !== '')
                    Status: {{ $status == '1' ? 'Active' : 'Inactive' }}
                @endif
            </div>
        @endif
    </div>
    
    <div class="stats-container">
        <div class="stat-box">
            <h3>{{ $stats['total_users'] }}</h3>
            <p>Total Users</p>
        </div>
        <div class="stat-box">
            <h3>{{ $stats['active_users'] }}</h3>
            <p>Active Users</p>
        </div>
        <div class="stat-box">
            <h3>{{ $stats['users_with_subscriptions'] }}</h3>
            <p>With Subscriptions</p>
        </div>
        <div class="stat-box">
            <h3>{{ $stats['users_this_month'] }}</h3>
            <p>New This Month</p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Subscription</th>
                <th>Created On</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>
                        @if($user->status ?? 1)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $activeSubscription = $user->activeSubscription();
                        @endphp
                        
                        @if($activeSubscription)
                            @if($activeSubscription->isActiveSubscription())
                                <span class="badge badge-success">Active</span>
                            @elseif($activeSubscription->isTrialSubscription())
                                <span class="badge badge-info">Trial</span>
                            @elseif($activeSubscription->isCanceled())
                                <span class="badge badge-secondary">Canceled</span>
                            @elseif($activeSubscription->isExpired())
                                <span class="badge badge-warning">Expired</span>
                            @endif
                            {{ $activeSubscription->package->name ?? 'N/A' }}
                        @else
                            <span class="badge badge-secondary">None</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center;">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>JurisLocator Users Report &copy; {{ now()->format('Y') }} | Confidential</p>
        <p>Page 1</p>
    </div>
</body>
</html>
