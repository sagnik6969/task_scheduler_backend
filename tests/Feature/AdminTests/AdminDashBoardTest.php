<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssginmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use Tests\TestCase;

class AdminDashBoardTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    // public function test_unauthenticated_redirected_to_login()
    // {
    //     $response = $this->get('/admin');

    //     $response->assertStatus(302);

    //     $response->assertRedirect('/login');
    // }


    public function test_allows_authenticated_users_to_access_protected_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/api/admin');

        $response->assertStatus(302);

        $response->assertRedirect('/');
    }

    public function test_authenticated_admin_can_access_admin_page()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }
    public function test_Index()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $response = $this->get('/api/admin/tasks');
        $user = User::factory()->create();

        $response = $this->getJson('/api/admin/tasks');

        $response->assertStatus(200)
            ->assertJson([
                'users' => [
                    [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at->toISOString(),
                        'created_at' => $user->created_at->toISOString(),
                        'updated_at' => $user->updated_at->toISOString(),
                        'is_admin' => 0,
                        'completed_tasks' => 0,
                        'incomplete_tasks' => 0,
                        'tasks' => []
                    ],
                ],
            ]);
    }
    public function test_userTasks()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $response = $this->get('/api/admin/users/' . $user->id);
        $response->assertStatus(200);
        $response->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at->toISOString(),
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
                'is_admin' => 0,
                'tasks' => [
                    [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'deadline' => $task->deadline->format('Y-m-d H:i:s'),
                        'is_completed' => (bool)$task->is_completed,
                        'progress' => $task->progress,
                        'priority' => $task->priority,
                        'user_id' => $task->user_id,
                        'admin_id' => $task->admin_id,
                        'created_at' => $task->created_at->toISOString(),
                        'updated_at' => $task->updated_at->toISOString(),
                    ],
                ],
            ],
        ]);
    }
    public function test_delete_task()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $response = $this->delete('/api/admin/tasks/' . $task->id);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Task deleted successfully']);
    }
    public function test_assigntask_to_user()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $user = User::factory()->create();
        Notification::fake();
        $response = $this->post('/api/admin/assign-task/' . $user->id, [
            'title' => 'Sample Task Title',
            'description' => 'Sample Task Description',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'priority' => 'Normal',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => "Task Details are sent successfully to {$user->name}"]);
        $this->assertDatabaseHas('admin_assign_tasks', [
            'title' => 'Sample Task Title',
            'description' => 'Sample Task Description',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'priority' => 'Normal',
        ]);

        Notification::assertSentTo(
            $user,
            TaskAssginmentNotification::class,
            function ($notification, $channels) use ($user) {
                return $notification->user->id === $user->id;
            }
        );
    }
    public function test_alluser_analysis()
    {
        $this->withoutExceptionHandling();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Task::factory()->create(['user_id' => $user1->id, 'is_completed' => true]);
        Task::factory()->create(['user_id' => $user1->id, 'is_completed' => false]);
        Task::factory()->create(['user_id' => $user2->id, 'is_completed' => true]);
        Task::factory()->create(['user_id' => $user2->id, 'is_completed' => false]);

        $response = $this->get('/api/admin/analysis');
        $response->assertStatus(200);
        $response->assertJson([
            [
                'id' => $user1->id,
                'name' => $user1->name,
                'email' => $user1->email,
                'is_admin' => 0,
                'created_at' => $user1->created_at->toISOString(),
                'updated_at' => $user1->updated_at->toISOString(),
                'incomplete_task_count' => 1,
                'complete_task_count' => 1,
            ],
            [
                'id' => $user2->id,
                'name' => $user2->name,
                'email' => $user2->email,
                'is_admin' => 0,
                'created_at' => $user2->created_at->toISOString(),
                'updated_at' => $user2->updated_at->toISOString(),
                'incomplete_task_count' => 1,
                'complete_task_count' => 1,
            ],
        ]);
    }
    public function test_make_Admin()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->patch("/api/admin/users/{$user->id}");
        $response->assertStatus(200);
        $user->is_admin = 1;
        $response->assertJson([
            'message' => 'User {$user->id} is now admin'
        ]);
    }
    public function test_all_user_progress_analysis()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->get('/api/admin/analysis/all_user_task_progress_analysis');

        $response->assertStatus(200);

        $responseData = $response->json();

        $expectedData = [
            "series" => $responseData['series'],
            "labels" => $responseData['labels'],
        ];

        $response->assertJson($expectedData);
    }
}
