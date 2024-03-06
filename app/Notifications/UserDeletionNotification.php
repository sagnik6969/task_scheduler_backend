<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class UserDeletionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = url("http://localhost:5173/register");
        return (new MailMessage)
            ->subject('Temporaty Account Deleted')
            ->greeting("Dear {$notifiable->name},")
            ->line("We regret to infrom you that your account is temporarily deleted by admin . ")
            ->line('Due to your actions . So we give you another chance to again register freshly')
            ->action('Register Now', $url)
            ->line("Thank you for using " . config('app.name') . " app!");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
