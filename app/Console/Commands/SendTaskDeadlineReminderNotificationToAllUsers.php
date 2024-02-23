<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDeadlineReminderNotification;
use Illuminate\Console\Command;

class SendTaskDeadlineReminderNotificationToAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-task-deadline-reminder-notification-to-all-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::where('deadline_notification_sent', 0)
            ->where('deadline', '<=', now()->addHour())
            ->where('is_completed', 0)
            ->get();

        $tasks->each(function ($task) {
            $task->user->notify(new TaskDeadlineReminderNotification($task));
            $task->deadline_notification_sent = true;
            $task->save();
        });
    }
}
