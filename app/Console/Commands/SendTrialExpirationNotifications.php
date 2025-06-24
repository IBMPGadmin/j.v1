<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSubscription;
use App\Notifications\TrialExpiringNotification;
use Carbon\Carbon;

class SendTrialExpirationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:notify-trial-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users whose trial is about to expire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all active trial subscriptions
        $trialSubscriptions = UserSubscription::where('status', 'trial')
            ->where('trial_ends_at', '>', Carbon::now())
            ->with('user')
            ->get();
            
        $now = Carbon::now();
        $notificationsSent = 0;
        
        foreach ($trialSubscriptions as $subscription) {
            // Calculate days left in trial
            $daysLeft = $now->diffInDays($subscription->trial_ends_at, false);
            
            // Send notification at 7 days, 3 days, and 1 day before expiration
            if (in_array($daysLeft, [7, 3, 1])) {
                $user = $subscription->user;
                
                if ($user) {
                    $user->notify(new TrialExpiringNotification($subscription, $daysLeft));
                    $notificationsSent++;
                    
                    $this->info("Notification sent to {$user->email} ({$daysLeft} days left)");
                }
            }
        }
        
        $this->info("Completed sending trial expiration notifications. Total sent: {$notificationsSent}");
        
        return Command::SUCCESS;
    }
}
