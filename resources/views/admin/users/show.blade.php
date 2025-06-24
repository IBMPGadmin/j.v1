@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0">User Details</h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
                        <i class="ti ti-arrow-left"></i> Back to Users
                    </a>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-4">
                                @if($user->profile_image)
                                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px; font-size: 48px; color: #6c757d;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <h4 class="mt-3">{{ $user->name }}</h4>
                                <p class="text-muted">{{ ucfirst($user->role) }}</p>
                                
                                <div class="mt-3">
                                    @if($user->status ?? 1)
                                        <span class="badge bg-success fs-6 px-3 py-2">Active</span>
                                    @else
                                        <span class="badge bg-danger fs-6 px-3 py-2">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="border rounded p-3 bg-light">
                                <h5 class="border-bottom pb-2">Contact Information</h5>
                                <div class="mb-2">
                                    <label class="fw-bold">Email:</label>
                                    <div>{{ $user->email }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="fw-bold">Member Since:</label>
                                    <div>{{ $user->created_at->format('F d, Y') }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="fw-bold">Last Updated:</label>
                                    <div>{{ $user->updated_at->format('F d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <!-- Subscription Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="m-0">Subscription Status</h5>
                                </div>
                                <div class="card-body">
                                    @if($activeSubscription)
                                        <div class="alert {{ $activeSubscription->isExpired() ? 'alert-warning' : 'alert-info' }}">
                                            @if($activeSubscription->isActiveSubscription())
                                                <strong>Active Subscription</strong>
                                            @elseif($activeSubscription->isTrialSubscription())
                                                <strong>Trial Subscription</strong>
                                            @elseif($activeSubscription->isCanceled())
                                                <strong>Canceled Subscription</strong>
                                            @elseif($activeSubscription->isExpired())
                                                <strong>Expired Subscription</strong>
                                            @endif
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Package:</strong> {{ $activeSubscription->package->name ?? 'N/A' }}</p>
                                                <p><strong>Price:</strong> ${{ number_format($activeSubscription->package->price ?? 0, 2) }}</p>
                                                <p><strong>Payment Status:</strong> 
                                                    <span class="badge {{ $activeSubscription->payment_status == 'completed' ? 'bg-success' : 'bg-warning' }}">
                                                        {{ ucfirst($activeSubscription->payment_status) }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                @if($activeSubscription->trial_starts_at)
                                                    <p><strong>Trial Started:</strong> {{ $activeSubscription->trial_starts_at->format('M d, Y') }}</p>
                                                    <p><strong>Trial Ends:</strong> {{ $activeSubscription->trial_ends_at->format('M d, Y') }}</p>
                                                @endif
                                                @if($activeSubscription->subscription_starts_at)
                                                    <p><strong>Subscription Started:</strong> {{ $activeSubscription->subscription_starts_at->format('M d, Y') }}</p>
                                                    <p><strong>Subscription Ends:</strong> {{ $activeSubscription->subscription_ends_at->format('M d, Y') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            This user does not have any subscription.
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Subscription History -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="m-0">Subscription History</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Package</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Expires</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($user->subscriptions as $index => $subscription)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $subscription->package->name ?? 'N/A' }}</td>
                                                        <td>${{ number_format($subscription->package->price ?? 0, 2) }}</td>
                                                        <td>
                                                            @if($subscription->isActiveSubscription())
                                                                <span class="badge bg-success">Active</span>
                                                            @elseif($subscription->isTrialSubscription())
                                                                <span class="badge bg-info">Trial</span>
                                                            @elseif($subscription->isCanceled())
                                                                <span class="badge bg-secondary">Canceled</span>
                                                            @elseif($subscription->isExpired())
                                                                <span class="badge bg-warning">Expired</span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ $subscription->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $subscription->created_at->format('M d, Y') }}</td>
                                                        <td>{{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'N/A' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No subscription history found.</td>
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
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}" style="display:inline-block;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn {{ ($user->status ?? 1) ? 'btn-warning' : 'btn-success' }}">
                                    {{ ($user->status ?? 1) ? 'Deactivate User' : 'Activate User' }}
                                </button>
                            </form>
                        </div>
                        
                        <div>
                            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="ti ti-trash"></i> Delete User
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
