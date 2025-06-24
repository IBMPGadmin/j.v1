@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-md-3">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-light-primary">
                                    <div class="avatar-texts">
                                        <i class="ti ti-users" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ $stats['total_users'] }}</h5>
                                    <p class="text-muted mb-0">Total Users</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-light-success">
                                    <div class="avatar-texts">
                                        <i class="ti ti-user-check" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ $stats['active_users'] }}</h5>
                                    <p class="text-muted mb-0">Active Users</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-light-warning">
                                    <div class="avatar-texts">
                                        <i class="ti ti-credit-card" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ $stats['users_with_subscriptions'] }}</h5>
                                    <p class="text-muted mb-0">With Subscriptions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-light-info">
                                    <div class="avatar-texts">
                                        <i class="ti ti-user-plus" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ $stats['users_this_month'] }}</h5>
                                    <p class="text-muted mb-0">New This Month</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Users Report</h5>
                    <div>
                        <a href="{{ route('admin.reports.users.export', request()->query()) }}" class="btn btn-light">
                            <i class="ti ti-file-download me-1"></i> Download PDF
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filters -->
                    <div class="mb-4">
                        <form action="{{ route('admin.reports.users') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by name or email" name="search" value="{{ request('search') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <select class="form-select" name="role">
                                    <option value="">All Roles</option>
                                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <input type="date" class="form-control" placeholder="Start Date" name="start_date" value="{{ request('start_date') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <input type="date" class="form-control" placeholder="End Date" name="end_date" value="{{ request('end_date') }}">
                            </div>
                            
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Subscription</th>
                                    <th>Created On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ ucfirst($user->role) }}</td>
                                        <td>
                                            @if($user->status ?? 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $activeSubscription = $user->activeSubscription();
                                            @endphp
                                            
                                            @if($activeSubscription)
                                                @if($activeSubscription->isActiveSubscription())
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($activeSubscription->isTrialSubscription())
                                                    <span class="badge bg-info">Trial</span>
                                                @elseif($activeSubscription->isCanceled())
                                                    <span class="badge bg-secondary">Canceled</span>
                                                @elseif($activeSubscription->isExpired())
                                                    <span class="badge bg-warning">Expired</span>
                                                @endif
                                                <span class="ms-1 small">{{ $activeSubscription->package->name ?? 'N/A' }}</span>
                                            @else
                                                <span class="badge bg-secondary">None</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info">
                                                <i class="ti ti-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No users found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
