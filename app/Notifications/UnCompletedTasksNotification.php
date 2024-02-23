<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UnCompletedTasksNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    private $tasks;
    public function __construct($tasks)
    {
        $this->tasks = $tasks;
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
    public function toMail(object $notifiable): MailMessage
    {
        $taskNames = [];
        foreach ($this->tasks as $task) {
            $taskNames[] = $task->title;
        }

        return (new MailMessage)
            ->subject('Your Daily Task Reminder')->markdown('email', [
                    'name' => $notifiable->name,
                    'tasks' => $taskNames,
                ]);
        // ->greeting('Dear ' . $notifiable->name)
        // ->line("Here's your daily task reminder:")
        // ->line($taskNames)
        // ->line('Please remember to complete these tasks at your earliest convenience. If you have already completed any of them, please mark them as done in your task list.')
        // ->line('Thank you for using our task list app!')
        // ->line('Best regards,')
        // ->line(env('APP_NAME'));
        // ->line('The introduction to the notification.')
        // ->action('Notification Action', url('/'))
        // ->line('Thank you for using our application!');
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
