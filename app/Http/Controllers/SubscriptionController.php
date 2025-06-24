<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPackage;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionPurchasedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{    public function __construct()
    {
        // Set Stripe API key directly from .env for more reliability
        $stripeSecret = env('STRIPE_SECRET');
        if (!$stripeSecret) {
            throw new \Exception('Stripe API key is not set in .env file');
        }
        Stripe::setApiKey($stripeSecret);
    }

    public function showPricing()
    {
        $packages = SubscriptionPackage::all();
        $user = Auth::user();
        $currentSubscription = $user ? $user->activeSubscription() : null;

        return view('subscription.pricing', [
            'packages' => $packages,
            'currentSubscription' => $currentSubscription,
        ]);
    }    public function purchase(Request $request, SubscriptionPackage $package)
    {
        $user = Auth::user();
        
        // Create a unique reference for this transaction
        $reference = 'sub_' . Str::random(10);
        
        try {
            // Store pending subscription
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_package_id' => $package->id,
                'starts_at' => now(),
                'expires_at' => null, // Lifetime subscription
                'status' => 'pending',
                'payment_status' => 'pending',
                'reference' => $reference,
            ]);
              // Create Stripe Checkout Session
            $stripeSecret = env('STRIPE_SECRET');
            $stripe = new \Stripe\StripeClient($stripeSecret);
            
            $checkout_session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => config('stripe.currency'),
                        'product_data' => [
                            'name' => $package->name,
                            'description' => 'Lifetime Access to JurisLocator',
                        ],
                        'unit_amount' => (int)($package->price * 100), // Amount in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('subscription.success', ['reference' => $reference]),
                'cancel_url' => route('subscription.cancel', ['reference' => $reference]),
                'customer_email' => $user->email,
            ]);
            
            return redirect()->away($checkout_session->url);
            
        } catch (\Exception $e) {
            // Log the error with detailed information
            logger()->error('Stripe session creation failed: ' . $e->getMessage());
            logger()->error('Error trace: ' . $e->getTraceAsString());
            
            // Delete the pending subscription if it was created
            if (isset($subscription)) {
                $subscription->delete();
            }
            
            // For development: show the actual error
            if (config('app.debug')) {
                return redirect()->back()->with('error', 'Payment processing failed: ' . $e->getMessage());
            }
            
            return redirect()->back()->with('error', 'Payment processing failed. Please try again later.');
        }
    }
      public function success(Request $request)
    {
        $reference = $request->reference;
        
        // Find the subscription by reference
        $subscription = UserSubscription::where('reference', $reference)
            ->where('status', 'pending')
            ->first();
            
        if (!$subscription) {
            return redirect()->route('subscription.pricing')
                ->with('error', 'Subscription not found or already processed.');
        }
          // Update subscription status
        $subscription->update([
            'status' => 'active',
            'payment_status' => 'completed',
        ]);
        
        // Send purchase notification
        $user = $subscription->user;
        if ($user) {
            try {
                $user->notify(new \App\Notifications\SubscriptionPurchasedNotification($subscription));
            } catch (\Exception $e) {
                // Log the error but don't stop the process
                logger()->error('Failed to send purchase notification: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('user.dashboard')
            ->with('success', 'Thank you! Your subscription has been activated successfully.');
    }
    
    public function cancel(Request $request)
    {
        $reference = $request->reference;
        
        // Find the subscription by reference
        $subscription = UserSubscription::where('reference', $reference)
            ->where('status', 'pending')
            ->first();
            
        if ($subscription) {
            // Delete the pending subscription
            $subscription->delete();
        }
        
        return redirect()->route('subscription.pricing')
            ->with('warning', 'Your payment was cancelled. Please try again when you are ready.');
    }
    
    public function testCards()
    {
        return view('subscription.test-cards');
    }
}
