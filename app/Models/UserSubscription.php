<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{    protected $fillable = [
        'user_id',
        'subscription_package_id',
        'starts_at',
        'expires_at',
        'status',
        'payment_status',
        'reference',
        'trial_starts_at',
        'trial_ends_at',
        'subscription_starts_at',
        'subscription_ends_at'
    ];

    protected $dates = [
        'trial_starts_at',
        'trial_ends_at',
        'subscription_starts_at',
        'subscription_ends_at',
        'starts_at',
        'expires_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }

    public function isInTrial()
    {
        if (!$this->trial_starts_at || !$this->trial_ends_at) {
            return false;
        }

        return Carbon::now()->between(
            $this->trial_starts_at,
            $this->trial_ends_at
        );
    }

    public function hasActiveSubscription()
    {
        if ($this->status === 'active') {
            return true;
        }

        if ($this->status === 'trial' && $this->trial_ends_at > now()) {
            return true;
        }

        return false;
    }

    public function isTrialSubscription()
    {
        return $this->status === 'trial';
    }

    public function isActiveSubscription()
    {
        return $this->status === 'active';
    }

    public function isExpiredSubscription()
    {
        return $this->status === 'expired';
    }

    /**
     * Check if the subscription is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at < now());
    }

    /**
     * Check if the subscription is canceled
     *
     * @return bool
     */
    public function isCanceled()
    {
        return $this->status === 'canceled' || $this->status === 'cancelled';
    }
}
