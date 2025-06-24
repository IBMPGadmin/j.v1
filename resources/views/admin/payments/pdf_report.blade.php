<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
        }
        .report-meta {
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-box {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            width: 22%;
            text-align: center;
        }
        .stat-title {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 10px;
            font-size: 14px;
        }
        td {
            padding: 10px;
            font-size: 13px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .filters {
            margin-bottom: 20px;
            font-size: 13px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 15px;
            padding: 5px 10px;
            background-color: #e9ecef;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>JurisLocator Payment Report</h1>
        <p>Generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
    
    <div class="report-meta">
        <p><strong>Report Period:</strong> {{ $startDate ?? 'All time' }} to {{ $endDate ?? 'Present' }}</p>
        @if($search)
        <p><strong>Search Query:</strong> {{ $search }}</p>
        @endif
        @if($status)
        <p><strong>Status Filter:</strong> {{ ucfirst($status) }}</p>
        @endif
        @if($packageName)
        <p><strong>Package Filter:</strong> {{ $packageName }}</p>
        @endif
    </div>
    
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-value">${{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Total Payments</div>
            <div class="stat-value">{{ $stats['total_payments'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Active Subscriptions</div>
            <div class="stat-value">{{ $stats['active_subscriptions'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Payments This Month</div>
            <div class="stat-value">{{ $stats['payments_this_month'] }}</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>                <th>User</th>
                <th>Package</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscriptions as $subscription)
            <tr>
                <td>{{ $subscription->id }}</td>
                <td>
                    {{ $subscription->user->name ?? 'Unknown' }}<br>
                    <small>{{ $subscription->user->email ?? 'N/A' }}</small>
                </td>
                <td>{{ $subscription->package->name ?? 'N/A' }}</td>                <td>{{ $subscription->amount ? '$'.number_format($subscription->amount, 2) : ($subscription->package ? '$'.number_format($subscription->package->price, 2) : 'N/A') }}</td>
                <td>{{ $subscription->created_at ? $subscription->created_at->format('M d, Y') : 'N/A' }}</td>
                <td>{{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'N/A' }}</td>
                <td>
                    @if($subscription->is_trial)
                    Trial
                    @elseif($subscription->isCanceled())
                    Canceled
                    @elseif($subscription->isExpired())
                    Expired
                    @else
                    Active
                    @endif
                </td>
                <td>{{ $subscription->payment_method ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">No payment records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} JurisLocator. All rights reserved.</p>
        <p>This is an auto-generated report. Please do not reply.</p>
    </div>
</body>
</html>
