<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'name',
        'type',
        'price',
        'duration_days',
        'description',
        'features',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean'
    ];    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'subscription_package_id');
    }
}
