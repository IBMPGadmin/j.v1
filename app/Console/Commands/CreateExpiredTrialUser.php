<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CreateExpiredTrialUser extends Command
{
    protected $signature = 'test:create-expired-trial-user';
    protected $description = 'Create a test user with an expired trial subscription';

    public function handle()
    {
        // Create a user
        $user = User::create([
            'name' => 'Test Expired',
            'email' => 'expired_trial_' . time() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Create expired trial subscription
        UserSubscription::create([
            'user_id' => $user->id,
            'trial_starts_at' => Carbon::now()->subDays(10),
            'trial_ends_at' => Carbon::now()->subDays(3), // Trial ended 3 days ago
            'status' => 'trial'
        ]);

        $this->info('Created test user with expired trial:');
        $this->info("Email: {$user->email}");
        $this->info("Password: password");
        $this->info("Trial ended: " . Carbon::now()->subDays(3)->format('Y-m-d H:i:s'));
    }
}
