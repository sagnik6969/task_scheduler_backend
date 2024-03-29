<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineReminderNotification extends Notification
{
    use Queueable;

    private $task;

    /**
     * Create a new notification instance.
     */
    public function __construct($task)
    {
        $this->task = $task;
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
            ->subject('Reminder: Task Deadline Approaching')
            ->greeting("Dear {$notifiable->name},")
            ->line('We wanted to remind you that you have a task with a deadline approaching in just one hour.')
            ->line('Here are the details:')
            ->line('Task Name: ' . $this->task->title)
            ->line('Deadline: ' . $this->task->deadline)
            ->line('Please make sure to complete the task within the next hour to stay on track.')
            ->line("Thank you for using " . env('app.name') . " app!");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'deadline_reminder',
            'text' => $this->task->title . " is due in 1 hour! don't forget to complete it"
        ];
    }
}
