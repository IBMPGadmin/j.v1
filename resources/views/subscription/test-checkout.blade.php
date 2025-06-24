@extends('layouts.user-layout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary">Stripe Test Checkout</h1>
            <p class="lead mt-3">
                This is a simple test page to verify Stripe integration is working correctly.
            </p>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Test Product</h3>
                </div>
                <div class="card-body">
                    <p class="mb-4">Click the button below to be redirected to the Stripe Checkout page for a test product.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ $checkoutUrl }}" class="btn btn-primary btn-lg">
                            Go to Stripe Checkout
                        </a>
                        <a href="{{ route('subscription.pricing') }}" class="btn btn-outline-secondary">
                            Return to Pricing Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
