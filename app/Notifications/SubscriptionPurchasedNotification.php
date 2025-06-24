<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\UserSubscription;

class SubscriptionPurchasedNotification extends Notification
{
    use Queueable;

    protected $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserSubscription $subscription)
    {
        $this->subscription = $subscription;
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
        $purchaseDate = $this->subscription->created_at->format('F j, Y');
        $packageName = $this->subscription->package->name;
        $price = number_format($this->subscription->package->price, 2);
        
        return (new MailMessage)
            ->subject('Thank You for Your JurisLocator Subscription Purchase')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for purchasing a JurisLocator subscription!')
            ->line("Package: {$packageName}")
            ->line("Price: ${$price}")
            ->line("Purchase Date: {$purchaseDate}")
            ->line('You now have full access to all JurisLocator features.')
            ->action('View Your Subscription Details', route('payment.details'))
            ->line('If you have any questions or need assistance, please contact our support team.')
            ->line('Thank you for choosing JurisLocator!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'package_id' => $this->subscription->subscription_package_id,
            'purchase_date' => $this->subscription->created_at,
        ];
    }
}
