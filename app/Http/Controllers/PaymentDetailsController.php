<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSubscription;
use App\Models\SubscriptionPackage;
use Illuminate\Support\Facades\Auth;

class PaymentDetailsController extends Controller
{
    /**
     * Display the user's payment details and subscription history
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's subscriptions with package information
        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();
          // Get available packages for upgrading or purchasing new subscriptions
        $availablePackages = SubscriptionPackage::where('is_active', true)->get();
        
        return view('payment.details', [
            'subscriptions' => $subscriptions,
            'availablePackages' => $availablePackages,
            'title' => 'Payment Details',
        ]);
    }
      /**
     * Cancel a subscription
     */
    public function cancelSubscription(Request $request, $id)
    {
        $subscription = UserSubscription::findOrFail($id);
        
        // Check if the subscription belongs to the authenticated user
        if ($subscription->user_id !== Auth::id()) {
            return redirect()->route('payment.details')
                ->with('error', 'You do not have permission to cancel this subscription.');
        }
        
        // Only allow cancellation of active subscriptions
        if (!$subscription->is_active) {
            return redirect()->route('payment.details')
                ->with('error', 'This subscription is already inactive.');
        }
        
        // Mark as canceled but keep the record
        $subscription->update([
            'is_active' => false,
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);
        
        return redirect()->route('payment.details')
            ->with('success', 'Subscription has been canceled successfully.');
    }
    
    /**
     * Activate a new package - redirects to the subscription controller purchase method
     */
    public function activateNewPackage(Request $request, $packageId)
    {
        // Find the package
        $package = SubscriptionPackage::findOrFail($packageId);
        
        // Redirect to the subscription purchase page
        return redirect()->route('subscription.purchase', $package->id);
    }
}
