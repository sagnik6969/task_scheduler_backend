<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssginmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Notification;
use Tests\TestCase;

class AdminDashBoardTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

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
                        'is_completed' => (bool) $task->is_completed,
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
        Sanctum::actingAs($admin);
        $user = User::factory([
            'is_admin' => 0
        ])->create();
        Notification::fake();
        $response = $this->post('/api/admin/assign-task/' . $user->id, [
            'title' => 'Sample Task Title',
            'description' => 'Sample Task Description',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'priority' => 'Normal',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => "Task Detailes are sent successfully to {$user->name}"]);
        $this->assertDatabaseHas('admin_assign_tasks', [
            'title' => 'Sample Task Title',
            'description' => 'Sample Task Description',
            'deadline' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'priority' => 'Normal',
        ]);

        Notification::assertSentTo($user, TaskAssginmentNotification::class);
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
