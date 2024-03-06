<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeletionNotification extends Notification
{
    use Queueable;
    private $task;
    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    { 
        $this->task = $task;
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
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Task Deleted By Admin')
            ->greeting("Dear {$notifiable->name},")
            ->line('Here are the details:')
            ->line('Task Name: ' . $this->task->title)
            ->line('Deadline: ' . $this->task->deadline)
            ->line('--- Your Task is deleted by admin ---')
            ->line('Please log in to your account to check out updates.')
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
            'type' => 'task_assignment_notification',
            'text' => `Your task scheduled on {$this->task->deadline} `,
        ];
    }
}
