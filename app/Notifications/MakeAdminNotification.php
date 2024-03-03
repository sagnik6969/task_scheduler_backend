<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MakeAdminNotification extends Notification
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = env('APP_NAME', "task scheduler");
        return(new MailMessage)
            ->subject("You've been promoted to Admin!")
            ->greeting("Dear {$notifiable->name}")
            ->line("We're thrilled to announce that you've been promoted to an Admin role in {$appName}! 🎉")
            ->line("As an Admin, you'll have additional responsibilities, including task assignment, user management, and ensuring smooth operation")
            ->line("If you have any questions or need assistance with your new responsibilities, feel free to reach out to us. We're here to support you every step of the way.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'make_admin_notification',
            'text' => "🎉 Congrats! You're now an Admin"
        ];
    }
}
