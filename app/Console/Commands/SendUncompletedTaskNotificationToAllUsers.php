<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\UnCompletedTasksNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SendUncompletedTaskNotificationToAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-uncompleted-task-notification-to-all-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sends a notification to all users mentioning their uncompleted tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $usersWithIncompleteTasks = User::with(
            ['tasks' => fn($query) => $query->where('is_completed', 0)]
        );

        $usersWithIncompleteTasks->each(function ($user) {
            // foreach ($user->tasks as $task) {
            //     $this->info("{$user->name} => {$task->title}");
            // }
            if (count($user->tasks))
                $user->notify(new UnCompletedTasksNotification($user->tasks));
        });


    }
}
