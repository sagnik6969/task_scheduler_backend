<?php

namespace Tests\Feature;

use App\Console\Commands\SendTaskDeadlineReminderNotificationToAllUsers;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class SendTaskDeadlineReminderNotificationToAllUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_appropriate_task_dead_line_reminder_is_sent_when_the_command_is_executed()
    {
        Notification::fake();
        $user = User::factory()->create();
        Task::factory(2)->create([
            'user_id' => $user->id,
            'deadline' => now()->addMinute(),
            'is_completed' => 0
        ]);

        Task::factory()->create([
            'user_id' => $user->id,
            'deadline' => now()->addDay()
        ]);

        $this->artisan('app:send-task-deadline-reminder-notification-to-all-users')->assertOk();
        Notification::assertCount(2);
        Notification::assertSentTo([$user], TaskDeadlineReminderNotification::class);
    }

    public function test_if_a_task_is_already_completed_then_notification_is_not_sent()
    {
        Notification::fake();
        $user = User::factory()->create();
        Task::factory(2)->create([
            'user_id' => $user->id,
            'deadline' => now()->addMinute(),
            'is_completed' => 1
        ]);

        $this->artisan('app:send-task-deadline-reminder-notification-to-all-users')->assertOk();
        Notification::assertCount(0);
        // Notification::assertSentTo([$user], TaskDeadlineReminderNotification::class);
    }

    public function test_if_more_than_1_hour_remaining_from_task_deadline_then_notification_is_not_sent()
    {
        Notification::fake();
        $user = User::factory()->create();
        Task::factory(2)->create([
            'user_id' => $user->id,
            'deadline' => now()->addHours(2),
            'is_completed' => 0
        ]);

        $this->artisan('app:send-task-deadline-reminder-notification-to-all-users')->assertOk();
        Notification::assertCount(0);
    }


}
