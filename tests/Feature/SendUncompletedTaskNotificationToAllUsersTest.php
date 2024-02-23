<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\UnCompletedTasksNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendUncompletedTaskNotificationToAllUsersTest extends TestCase
{
    use RefreshDatabase;
    public function test_appropriate_notification_is_sent_when_SendUncompletedTaskNotificationToAllUsers_command_is_executed()
    {
        Notification::fake();
        $user = User::factory()->create();
        Task::factory(1)->create([
            'user_id' => $user->id,
            'is_completed' => 0
        ]);

        $this->artisan('app:send-uncompleted-task-notification-to-all-users')->assertOk();
        Notification::assertCount(1);
        Notification::assertSentTo([$user], UnCompletedTasksNotification::class);

    }

    public function test_only_1_notification_is_sent_for_multiple_incomplete_tasks()
    {
        Notification::fake();
        $user = User::factory()->create();
        Task::factory(2)->create([
            'user_id' => $user->id,
            'is_completed' => 0
        ]);

        $this->artisan('app:send-uncompleted-task-notification-to-all-users')->assertOk();
        Notification::assertCount(1);
        Notification::assertSentTo([$user], UnCompletedTasksNotification::class);

    }

    public function test_notification_is_not_sent_when_there_is_no_task()
    {
        Notification::fake();
        $this->artisan('app:send-uncompleted-task-notification-to-all-users')->assertOk();
        Notification::assertCount(0);
    }

    public function test_notification_is_not_sent_when_there_is_no_incomplete_tasks()
    {
        Notification::fake();
        $user = User::factory()->create();
        Task::factory(2)->create([
            'user_id' => $user->id,
            'is_completed' => 1
        ]);

        $this->artisan('app:send-uncompleted-task-notification-to-all-users')->assertOk();
        Notification::assertCount(0);
        // Notification::assertSentTo([$user], UnCompletedTasksNotification::class);

    }

}
