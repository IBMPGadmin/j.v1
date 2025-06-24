<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\UserSubscription;

class TrialExpiringNotification extends Notification
{
    use Queueable;

    protected $subscription;
    protected $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserSubscription $subscription, int $daysLeft)
    {
        $this->subscription = $subscription;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expiryDate = $this->subscription->trial_ends_at ? $this->subscription->trial_ends_at->format('F j, Y') : 'soon';
        
        $mailMessage = (new MailMessage)
            ->subject('Your JurisLocator Trial is Expiring Soon')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your JurisLocator free trial is expiring soon.');

        if ($this->daysLeft > 1) {
            $mailMessage->line('You have ' . $this->daysLeft . ' days remaining in your trial period ending on ' . $expiryDate . '.');
        } else if ($this->daysLeft == 1) {
            $mailMessage->line('You have 1 day remaining in your trial period ending on ' . $expiryDate . '.');
        } else {
            $mailMessage->line('Your trial period ends today on ' . $expiryDate . '.');
        }

        return $mailMessage
            ->line('To continue using all the premium features of JurisLocator, please purchase a subscription.')
            ->action('View Subscription Options', route('subscription.pricing'))
            ->line('Thank you for using JurisLocator!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'days_left' => $this->daysLeft,
            'expiry_date' => $this->subscription->trial_ends_at,
        ];
    }
}
