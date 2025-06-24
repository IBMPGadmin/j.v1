@extends('layouts.user-layout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary">Stripe Test Cards</h1>
            <p class="lead mt-3">
                Use these test cards to simulate different payment scenarios in the test environment.
            </p>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Development Mode:</strong> JurisLocator is currently in development mode with Stripe test integration.
            </div>
        </div>

        <div class="col-12 mb-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Test Credit Cards</h3>
                </div>
                <div class="card-body">
                    <p class="mb-4">For all test cards, you can use any future expiration date, any 3-digit CVC, and any postal code.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Card Number</th>
                                    <th>Brand</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>4242 4242 4242 4242</code></td>
                                    <td>Visa</td>
                                    <td><span class="badge bg-success">Payment succeeds</span></td>
                                </tr>
                                <tr>
                                    <td><code>4000 0000 0000 0002</code></td>
                                    <td>Visa</td>
                                    <td><span class="badge bg-danger">Card declined</span></td>
                                </tr>
                                <tr>
                                    <td><code>4000 0000 0000 9995</code></td>
                                    <td>Visa</td>
                                    <td><span class="badge bg-warning text-dark">Insufficient funds</span></td>
                                </tr>
                                <tr>
                                    <td><code>4000 0000 0000 3220</code></td>
                                    <td>Visa</td>
                                    <td><span class="badge bg-secondary">3D Secure authentication</span></td>
                                </tr>
                                <tr>
                                    <td><code>5555 5555 5555 4444</code></td>
                                    <td>Mastercard</td>
                                    <td><span class="badge bg-success">Payment succeeds</span></td>
                                </tr>
                                <tr>
                                    <td><code>3782 8224 6310 005</code></td>
                                    <td>American Express</td>
                                    <td><span class="badge bg-success">Payment succeeds</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 mb-5">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0">Testing Instructions</h3>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered mb-4">
                        <li class="list-group-item">Select a subscription package from the <a href="{{ route('subscription.pricing') }}" class="text-primary">pricing page</a></li>
                        <li class="list-group-item">Click "Choose Plan" to proceed to the payment screen</li>
                        <li class="list-group-item">Use one of the test card numbers from the table above</li>
                        <li class="list-group-item">Enter any future expiration date (MM/YY)</li>
                        <li class="list-group-item">Enter any 3-digit CVC code</li>
                        <li class="list-group-item">Enter any name and postal code</li>
                        <li class="list-group-item">Click "Pay" to complete the test transaction</li>
                    </ol>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Do not use real credit cards in this test environment.
                    </div>
                </div>
            </div>
        </div>
          <div class="col-12 text-center">
            <a href="{{ route('subscription.pricing') }}" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-arrow-left me-2"></i> Return to Pricing Page
            </a>
            <a href="{{ route('subscription.test-checkout') }}" class="btn btn-success btn-lg">
                <i class="bi bi-credit-card me-2"></i> Try Test Checkout
            </a>
        </div>
    </div>
</div>
@endsection
