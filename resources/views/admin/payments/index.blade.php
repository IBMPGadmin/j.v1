@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid">
    <!-- Page header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="page-title">Payment Dashboard</h2>
                <p class="text-muted">View and manage all payment transactions</p>
            </div>
            <div class="col-md-6 text-end">
                <button id="export-pdf" class="btn btn-primary">
                    <i class="ti ti-file-download me-1"></i> Export to PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics cards -->
    <div class="row">
        <div class="col-sm-6 col-xl-3">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-primary">
                                <i class="ti ti-currency-dollar f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Revenue</h6>
                            <p class="text-primary mb-0">${{ number_format($stats['total_revenue'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-success">
                                <i class="ti ti-receipt f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Payments</h6>
                            <p class="text-success mb-0">{{ $stats['total_payments'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-warning">
                                <i class="ti ti-users f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Active Subscriptions</h6>
                            <p class="text-warning mb-0">{{ $stats['active_subscriptions'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-danger">
                                <i class="ti ti-calendar f-20"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Payments This Month</h6>
                            <p class="text-danger mb-0">{{ $stats['payments_this_month'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.payments.index') }}" method="GET" id="search-form">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by user name or email" name="search" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-package"></i></span>
                            <select class="form-select" name="package">
                                <option value="">All Packages</option>
                                @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ request('package') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-activity"></i></span>
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-calendar-event"></i></span>
                            <input type="date" class="form-control" placeholder="Start Date" name="start_date" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-calendar-event"></i></span>
                            <input type="date" class="form-control" placeholder="End Date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-filter me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>                            <th>ID</th>
                            <th>User</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($subscription->user && $subscription->user->profile_image)
                                    <img src="{{ asset('storage/' . $subscription->user->profile_image) }}" alt="Profile" class="rounded-circle me-2" width="35" height="35">
                                    @else
                                    <div class="avtar avtar-xs bg-light-primary rounded-circle me-2">
                                        <i class="ti ti-user f-16"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-0">{{ $subscription->user->name ?? 'Unknown' }}</h6>
                                        <small class="text-muted">{{ $subscription->user->email ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $subscription->package->name ?? 'N/A' }}</td>                            <td>{{ $subscription->amount ? '$'.number_format($subscription->amount, 2) : ($subscription->package ? '$'.number_format($subscription->package->price, 2) : 'N/A') }}</td>
                            <td>{{ $subscription->created_at ? $subscription->created_at->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                @if($subscription->is_trial)
                                <span class="badge bg-light-info">Trial</span>
                                @elseif($subscription->isCanceled())
                                <span class="badge bg-light-danger">Canceled</span>
                                @elseif($subscription->isExpired())
                                <span class="badge bg-light-warning">Expired</span>
                                @else
                                <span class="badge bg-light-success">Active</span>
                                @endif
                            </td>
                            <td>{{ $subscription->payment_method ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.payments.view', $subscription->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No payment records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $subscriptions->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('export-pdf').addEventListener('click', function() {
        // Clone the current search parameters
        const params = new URLSearchParams(window.location.search);
        params.append('export', 'pdf');
        
        // Create the PDF export URL
        const exportUrl = '{{ route("admin.payments.export") }}?' + params.toString();
        
        // Open in a new tab
        window.open(exportUrl, '_blank');
    });
</script>
@endsection
