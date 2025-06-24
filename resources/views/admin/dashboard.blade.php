@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid py-4">
    <!-- Dashboard Stats Row -->
    <div class="row">        <div class="col-sm-6 col-md-3">
            <div class="card mb-3 border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-light-primary">
                            <div class="avatar-texts">
                                <i class="ti ti-users" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $totalUsers }}</h5>
                            <p class="text-muted mb-0">Total Users</p>
                        </div>
                    </div>
                    <div class="progress mt-3 mb-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, ($activeUsers / max(1, $totalUsers)) * 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-success"><i class="ti ti-user-check me-1"></i>{{ $activeUsers }} Active</small>
                        <small class="text-danger"><i class="ti ti-user-x me-1"></i>{{ $inactiveUsers }} Inactive</small>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary w-100">View All Users</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card mb-3 border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-light-success">
                            <div class="avatar-texts">
                                <i class="ti ti-credit-card" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $totalSubscriptions }}</h5>
                            <p class="text-muted mb-0">Total Subscriptions</p>
                        </div>
                    </div>
                    <div class="progress mt-3 mb-2" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, ($activeSubscriptions / max(1, $totalSubscriptions)) * 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-success"><i class="ti ti-check me-1"></i>{{ $activeSubscriptions }} Active</small>
                        <small class="text-warning"><i class="ti ti-alert-circle me-1"></i>{{ $expiredSubscriptions }} Expired</small>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-success w-100">Payment Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card mb-3 border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-light-warning">
                            <div class="avatar-texts">
                                <i class="ti ti-cash" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">${{ number_format($totalRevenue, 2) }}</h5>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                    <div class="progress mt-3 mb-2" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 85%"></div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-success"><i class="ti ti-chart-bar me-1"></i>${{ number_format($revenueThisMonth, 2) }} This Month</small>
                        <small class="text-muted"><i class="ti ti-chart-line me-1"></i>${{ number_format($revenueLastMonth, 2) }} Last Month</small>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.reports.users') }}" class="btn btn-sm btn-warning w-100">User Reports</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card mb-3 border-start border-info border-3">
                <div class="card-body">                    <div class="d-flex align-items-center">
                        <div class="avatar bg-light-info">
                            <div class="avatar-texts">
                                <i class="ti ti-user-check" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $activeSubscriptions }}</h5>
                            <p class="text-muted mb-0">Active Subscriptions</p>
                        </div>
                    </div>
                    <div class="progress mt-3 mb-2" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, ($activeSubscriptions / max(1, $totalSubscriptions)) * 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-info"><i class="ti ti-hourglass me-1"></i>{{ $trialSubscriptions }} Trial</small>
                        <small class="text-secondary"><i class="ti ti-x me-1"></i>{{ $canceledSubscriptions }} Canceled</small>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.payments.index', ['status' => 'active']) }}" class="btn btn-sm btn-info w-100">View Active</a>
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- Charts Row -->
    <div class="row">
        <!-- User Registration Chart -->
        <div class="col-md-6">
            <div class="card mb-4 overflow-hidden">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="ti ti-chart-bar me-2 text-primary"></i>User Registrations This Year</h5>
                    <span class="badge bg-primary">{{ array_sum($userMonthlyData) }} Users</span>
                </div>
                <div class="card-body">
                    <div id="userRegistrationChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Revenue Chart -->
        <div class="col-md-6">
            <div class="card mb-4 overflow-hidden">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="ti ti-chart-line me-2 text-success"></i>Monthly Revenue This Year</h5>
                    <span class="badge bg-success">${{ number_format(array_sum($revenueMonthlyData), 2) }}</span>
                </div>
                <div class="card-body">
                    <div id="revenueChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- User & Subscription Stats Row -->
    <div class="row">
        <!-- User Statistics Card -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0">User Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Total Users</h6>
                                <h4>{{ $totalUsers }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Active Users</h6>
                                <h4>{{ $activeUsers }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Inactive Users</h6>
                                <h4>{{ $inactiveUsers }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Admin Users</h6>
                                <h4>{{ $adminUsers }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Regular Users</h6>
                                <h4>{{ $regularUsers }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">New This Month</h6>
                                <h4>{{ $userMonthlyData[date('n')] }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">View All Users</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Subscription Statistics Card -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0">Subscription Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Total Subscriptions</h6>
                                <h4>{{ $totalSubscriptions }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Active Subscriptions</h6>
                                <h4>{{ $activeSubscriptions }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Trial Subscriptions</h6>
                                <h4>{{ $trialSubscriptions }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Expired Subscriptions</h6>
                                <h4>{{ $expiredSubscriptions }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">Canceled Subscriptions</h6>
                                <h4>{{ $canceledSubscriptions }}</h4>
                            </div>
                            <div class="border-bottom pb-2 mb-2">
                                <h6 class="fw-light">This Month Revenue</h6>
                                <h4>${{ number_format($revenueThisMonth, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-success">View Payments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Distribution Chart & Recent Users -->
    <div class="row">
        <!-- Package Distribution Chart -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0">Subscription Package Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="packageDistributionChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Recent Users -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Recent Users</h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentUsers as $user)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-light-{{ $user->status ?? 1 ? 'success' : 'danger' }} me-3">
                                            <div class="avatar-texts">
                                                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted"><i class="ti ti-mail me-1"></i>{{ $user->email }}</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge {{ $user->status ?? 1 ? 'bg-success' : 'bg-danger' }}">
                                            <i class="ti ti-{{ $user->status ?? 1 ? 'circle-check' : 'circle-x' }} me-1"></i>
                                            {{ $user->status ?? 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                        <small class="text-muted ms-2"><i class="ti ti-calendar me-1"></i>{{ $user->created_at->diffForHumans() }}</small>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info ms-2">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4">
                                <i class="ti ti-users text-muted" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">No recent users found.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Subscriptions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Recent Subscriptions</h5>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th>User</th>
                                    <th>Package</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSubscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs bg-light-primary me-2">
                                                    <span>{{ strtoupper(substr($subscription->user->name ?? 'U', 0, 1)) }}</span>
                                                </div>
                                                <a href="{{ route('admin.users.show', $subscription->user->id ?? 0) }}" class="fw-medium">
                                                    {{ $subscription->user->name ?? 'N/A' }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $subscription->package->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-medium">${{ number_format($subscription->package->price ?? 0, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($subscription->isActiveSubscription())
                                                <span class="badge bg-success"><i class="ti ti-check me-1"></i>Active</span>
                                            @elseif($subscription->isTrialSubscription())
                                                <span class="badge bg-info"><i class="ti ti-clock me-1"></i>Trial</span>
                                            @elseif($subscription->isCanceled())
                                                <span class="badge bg-secondary"><i class="ti ti-x me-1"></i>Canceled</span>
                                            @elseif($subscription->isExpired())
                                                <span class="badge bg-warning"><i class="ti ti-alert-triangle me-1"></i>Expired</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $subscription->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $subscription->payment_status == 'completed' ? 'bg-success' : 'bg-warning' }}">
                                                <i class="ti ti-{{ $subscription->payment_status == 'completed' ? 'credit-card-check' : 'credit-card' }} me-1"></i>
                                                {{ ucfirst($subscription->payment_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <i class="ti ti-calendar text-muted me-1"></i>
                                            {{ $subscription->created_at->format('M d, Y') }}
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.payments.view', $subscription->id) }}" class="btn btn-sm btn-info">
                                                <i class="ti ti-eye"></i> View
                                            </a>
                                        </td>                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="ti ti-credit-card text-muted" style="font-size: 2.5rem;"></i>
                                            <p class="mt-2 mb-0">No recent subscriptions found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- ApexCharts -->
<script src="{{ asset('admin_assets/js/plugins/apexcharts.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // User Registration Chart
        var userOptions = {
            chart: {
                height: 300,
                type: 'bar',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded',
                    borderRadius: 5
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val;
                },
                style: {
                    fontSize: '12px',
                    colors: ['#ffffff']
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            series: [{
                name: 'New Users',
                data: @json($userMonthlyData)
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            },
            yaxis: {
                title: {
                    text: 'Users'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " users"
                    }
                }
            },
            colors: ['#4680ff']
        };
        var userChart = new ApexCharts(document.querySelector("#userRegistrationChart"), userOptions);
        userChart.render();        // Revenue Chart
        var revenueOptions = {
            chart: {
                height: 300,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return "$" + val.toFixed(0);
                },
                style: {
                    fontSize: '12px'
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: [{
                name: 'Revenue',
                data: @json($revenueMonthlyData)
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            },
            yaxis: {
                title: {
                    text: 'Revenue ($)'
                },
                labels: {
                    formatter: function (value) {
                        return "$" + value.toFixed(0);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "$" + val.toFixed(2)
                    }
                }
            },
            colors: ['#2ecc71'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                    stops: [0, 90, 100]
                }
            }
        };
        var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
        revenueChart.render();        // Package Distribution Chart
        var packageOptions = {
            chart: {
                height: 300,
                type: 'pie',
            },
            labels: @json($packageLabels),
            series: @json($packageValues),
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            },
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex] + ' (' + val.toFixed(1) + '%)';
                },
                style: {
                    fontSize: '12px'
                },
                dropShadow: {
                    enabled: false
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            colors: ['#4680ff', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " subscriptions"
                    }
                }
            }
        };
        var packageChart = new ApexCharts(document.querySelector("#packageDistributionChart"), packageOptions);
        packageChart.render();
    });
</script>
@endpush
