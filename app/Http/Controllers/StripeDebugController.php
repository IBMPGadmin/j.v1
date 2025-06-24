<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeDebugController extends Controller
{
    public function checkConfig()
    {
        // Only show this in local/development environment
        if (!app()->environment('local')) {
            abort(404);
        }
        
        $stripePublicKey = env('STRIPE_KEY');
        $stripeSecretKey = env('STRIPE_SECRET');
        $stripeCurrency = env('STRIPE_CURRENCY', 'cad');
        
        return view('subscription.stripe-debug', [
            'publicKey' => $stripePublicKey,
            'secretKeySet' => !empty($stripeSecretKey),
            'secretKeyLength' => strlen($stripeSecretKey),
            'secretKeyPrefix' => substr($stripeSecretKey, 0, 7) . '...',
            'currency' => $stripeCurrency,
            'phpVersion' => PHP_VERSION,
            'stripeExtensionLoaded' => extension_loaded('curl'),
        ]);
    }
}
