@extends('layouts.user-layout')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Your Subscription Details</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4>Current Subscription Status</h4>
                            @php
                                $activeSubscription = $subscriptions->where('status', 'active')->first() 
                                    ?? $subscriptions->where('status', 'trial')
                                        ->where('trial_ends_at', '>', now())
                                        ->first();
                            @endphp

                            @if($activeSubscription)
                                <div class="card border-success mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title text-success">
                                                    {{ $activeSubscription->package->name }}
                                                    @if($activeSubscription->status === 'trial')
                                                        <span class="badge bg-info ms-2">Trial</span>
                                                    @else
                                                        <span class="badge bg-success ms-2">Active</span>
                                                    @endif
                                                </h5>
                                                <p class="card-text">
                                                    @if($activeSubscription->status === 'trial')
                                                        Trial period: {{ $activeSubscription->trial_starts_at->format('M d, Y') }} - 
                                                        {{ $activeSubscription->trial_ends_at->format('M d, Y') }}
                                                        <br>
                                                        <strong>Days remaining: {{ now()->diffInDays($activeSubscription->trial_ends_at, false) }}</strong>
                                                    @else
                                                        <strong>Lifetime Access</strong><br>
                                                        Purchased on: {{ $activeSubscription->created_at->format('M d, Y') }}
                                                    @endif
                                                </p>
                                            </div>
                                            @if($activeSubscription->status === 'active')
                                                <form action="{{ route('payment.subscription.cancel', $activeSubscription->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription? This action cannot be undone.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel Subscription</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    You don't have an active subscription. 
                                    <a href="{{ route('subscription.pricing') }}" class="alert-link">Purchase a subscription</a> to access all features.
                                </div>
                            @endif
                        </div>
                    </div>                    <!-- Available Packages Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4>Available Packages</h4>
                            @if($activeSubscription && $activeSubscription->status === 'trial')
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Your trial will expire soon. Purchase a subscription to continue accessing all features.
                            </div>
                            @elseif($activeSubscription && $activeSubscription->status === 'active')
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                You already have an active subscription, but you can purchase an additional package if needed.
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                You don't have an active subscription. Purchase a subscription to access all features.
                            </div>
                            @endif
                            
                            <div class="row">
                                @foreach($availablePackages as $package)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">{{ $package->name }}</h5>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title text-primary">${{ number_format($package->price, 2) }}</h5>
                                            <p class="card-text">{{ $package->description }}</p>
                                            <ul class="list-group list-group-flush mb-3">
                                                @foreach($package->features as $feature)
                                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i> {{ $feature }}</li>
                                                @endforeach
                                            </ul>
                                            <a href="{{ route('payment.subscription.activate', $package->id) }}" class="btn btn-primary mt-auto">Purchase Now</a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Subscription History Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Subscription History</h4>
                            @if($subscriptions->isEmpty())
                                <p class="text-muted">No subscription history found.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Package</th>
                                                <th>Status</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Payment Status</th>
                                                <th>Reference</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subscriptions as $subscription)
                                            <tr>
                                                <td>{{ $subscription->package->name }}</td>
                                                <td>
                                                    @if($subscription->status === 'active')
                                                        <span class="badge bg-success">Active</span>
                                                    @elseif($subscription->status === 'trial')
                                                        @if($subscription->trial_ends_at > now())
                                                            <span class="badge bg-info">Trial</span>
                                                        @else
                                                            <span class="badge bg-danger">Expired Trial</span>
                                                        @endif
                                                    @elseif($subscription->status === 'pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @elseif($subscription->status === 'canceled')
                                                        <span class="badge bg-danger">Canceled</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($subscription->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subscription->status === 'trial')
                                                        {{ $subscription->trial_starts_at ? $subscription->trial_starts_at->format('M d, Y') : 'N/A' }}
                                                    @else
                                                        {{ $subscription->created_at->format('M d, Y') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subscription->status === 'trial')
                                                        {{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y') : 'N/A' }}
                                                    @elseif($subscription->status === 'active')
                                                        Lifetime
                                                    @else
                                                        {{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'N/A' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subscription->payment_status === 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($subscription->payment_status === 'pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @elseif($subscription->payment_status === 'failed')
                                                        <span class="badge bg-danger">Failed</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($subscription->payment_status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $subscription->reference ?? 'N/A' }}</small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header h3 {
        font-weight: 600;
    }
    .table th {
        font-weight: 600;
    }
</style>
@endpush
