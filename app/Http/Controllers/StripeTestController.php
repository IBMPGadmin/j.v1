<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;

class StripeTestController extends Controller
{    public function testStripeCheckout()
    {
        try {
            $stripeSecret = env('STRIPE_SECRET');
            if (!$stripeSecret) {
                throw new \Exception('Stripe API key is not set in .env file');
            }
            
            $stripe = new \Stripe\StripeClient($stripeSecret);
            $checkout_session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'cad',
                        'product_data' => [
                            'name' => 'Test Product',
                            'description' => 'This is a test product',
                        ],
                        'unit_amount' => 2000, // $20.00
                    ],
                    'quantity' => 1,
                ]],                'mode' => 'payment',
                'success_url' => route('user.dashboard') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.pricing'),
            ]);
            
            return view('subscription.test-checkout', [
                'checkoutUrl' => $checkout_session->url
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
