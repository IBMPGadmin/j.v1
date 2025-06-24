<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            Log::info('CheckSubscription: User not authenticated');
            return redirect()->route('login');
        }        $user = Auth::user();
        $subscription = $user->subscriptions()->latest('id')->first();

        Log::info('CheckSubscription: Checking subscription', [
            'user_id' => $user->id,
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_ends_at,
                'now' => now()->toDateTimeString()
            ] : null
        ]);

        if (!$subscription) {
            Log::info('CheckSubscription: No subscription found, redirecting to pricing');
            return redirect()->route('subscription.pricing');
        }

        if (!$subscription->hasActiveSubscription()) {
            Log::info('CheckSubscription: No active subscription', [
                'status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_ends_at,
                'now' => now()
            ]);
            
            if ($subscription->status === 'trial' && $subscription->trial_ends_at < now()) {
                Log::info('CheckSubscription: Trial expired, updating status and redirecting');
                $subscription->update(['status' => 'expired']);
                return redirect()->route('subscription.pricing')->with('warning', 'Your trial period has expired. Please choose a subscription package to continue.');
            }

            Log::info('CheckSubscription: Subscription expired, redirecting');
            return redirect()->route('subscription.pricing')->with('warning', 'Your subscription has expired. Please renew to continue using the system.');
        }

        Log::info('CheckSubscription: Active subscription found, proceeding');
        return $next($request);
    }
}
