@extends('layouts.user-layout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary">Stripe Configuration Debug</h1>
            <p class="lead mt-3">
                This page helps verify your Stripe configuration.
            </p>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Development Only:</strong> This page should only be accessible in development mode.
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Stripe Configuration</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th scope="row">Public Key</th>
                                <td>
                                    @if($publicKey)
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-2"></i>Set</span>
                                        <small class="text-muted d-block">{{ $publicKey }}</small>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Not Set</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Secret Key</th>
                                <td>
                                    @if($secretKeySet)
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-2"></i>Set</span>
                                        <small class="text-muted d-block">{{ $secretKeyPrefix }} ({{ $secretKeyLength }} characters)</small>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Not Set</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Currency</th>
                                <td>{{ strtoupper($currency) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0">Environment Information</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th scope="row">PHP Version</th>
                                <td>{{ $phpVersion }}</td>
                            </tr>
                            <tr>
                                <th scope="row">cURL Extension</th>
                                <td>
                                    @if($stripeExtensionLoaded)
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-2"></i>Loaded</span>
                                    @else
                                        <span class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Not Loaded</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Environment</th>
                                <td>{{ app()->environment() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">Test Payments</h3>
                </div>
                <div class="card-body">
                    <p>Use the links below to test your Stripe integration:</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('subscription.test-checkout') }}" class="btn btn-primary">
                            <i class="bi bi-credit-card me-2"></i> Test Checkout
                        </a>
                        <a href="{{ route('subscription.test-cards') }}" class="btn btn-outline-primary">
                            <i class="bi bi-info-circle me-2"></i> View Test Cards
                        </a>
                        <a href="{{ route('subscription.pricing') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Return to Pricing
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
