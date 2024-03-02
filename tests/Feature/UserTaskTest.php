<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTaskTest extends TestCase
{
    use RefreshDatabase;
    public function test_can_create_a_task()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/api/user/tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task.',
            'deadline' => '2024-02-29 12:00:00',
            'is_completed' => false,
            'progress' => 0,
            'priority' => 'Normal',
        ]);
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'type' => 'create',
                'attributes' => [
                    'title' => 'Test Task',
                    'description' => 'This is a test task.',
                    'deadline' => '2024-02-29 12:00:00',
                    'is_completed' => false,
                    'progress' => 0,
                    'priority' => 'Normal',
                    'user_id' => $user->id,
                ],
            ],
        ]);
    }

    public function test_user_can_see_all_tasks()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/api/user/tasks');
        $response->assertStatus(200);

        $tasks = $user->tasks->toArray();

        $expectedData = array_map(
            function ($index, $task) {
                return [
                    'data' => [
                        'type' => strval($index),
                        'task_id' => $task['id'],
                        'attributes' => [
                            'title' => $task['title'],
                            'description' => $task['description'],
                            'deadline' => $task['deadline'],
                            'is_completed' => (bool)$task['is_completed'],
                            'progress' => $task['progress'],
                            'priority' => $task['priority'],
                            'user_id' => $task['user_id'],
                            'admin_id' => $task['admin_id'],
                            'created_at' => $task['created_at'],
                            'updated_at' => $task['updated_at'],
                        ],
                    ]
                ];
            },
            array_keys($tasks),
            $tasks
        );

        $response->assertJson([
            'type' => 'index',
            'data' => $expectedData,
            'links' => [
                'self' => url('/tasks'),
            ]
        ]);
    }
    public function test_a_user_can_update_task()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $taskid = $task->id;
        $response = $this->actingAs($user)->put("/api/user/tasks/" . $taskid, [
            'title' => 'Test Task',
            'description' => 'This is a test task.',
            'deadline' => '2024-02-29 12:00:00',
            'is_completed' => true,
            'progress' => 50,
            'priority' => 'Normal',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'type' => 'update',
                'attributes' => [
                    'title' => 'Test Task',
                    'description' => 'This is a test task.',
                    'deadline' => '2024-02-29 12:00:00',
                    'is_completed' => true,
                    'progress' => 50,
                    'priority' => 'Normal',
                    'user_id' => $user->id,
                ],
            ],
        ]);
    }
    public function test_a_user_can_delete_task()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $taskid = $task->id;
        $response = $this->actingAs($user)->delete("/api/user/tasks/" . $taskid);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Task deleted',
        ]);
    }
    public function test_a_user_can_see_task()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $taskid = $task->id;
        $response = $this->actingAs($user)->get("/api/user/tasks/" . $taskid);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'type' => 'show',
                'attributes' => [
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
        ]);
    }
    public function test_a_user_can_calculate_overall_efficiency()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $user->id, 'is_completed' => true]);
        $response = $this->actingAs($user)->get("/api/user/efficiency");
        $response->assertStatus(200);
        $response->assertJson([
            'total_tasks' => 3,
            'average_efficiency' => 5,
            'overall_efficiency_rating' => 'Excellent',
        ]);
    }
    public function test_user_tasks_analysis_completed_vs_pending_tasks()
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $user->id, 'is_completed' => true]);
        Task::factory()->count(2)->create(['user_id' => $user->id, 'is_completed' => false]);

        $response = $this->actingAs($user)
            ->get("/api/user/analysis?statistics=completed_vs_pending_tasks&time_range=all");

        $response->assertStatus(200)
            ->assertJson([
                'series' => [3, 2],
                'labels' => ['Completed Tasks', 'Incomplete Tasks']
            ]);
    }
    public function test_user_tasks_analysis_task_distribution_by_progress()
    {
        $user = User::factory()->create();
        Task::factory()->count(1)->create(['user_id' => $user->id, 'progress' => 10, 'is_completed' => false]);
        Task::factory()->count(2)->create(['user_id' => $user->id, 'progress' => 30, 'is_completed' => false]);
        Task::factory()->count(3)->create(['user_id' => $user->id, 'progress' => 60, 'is_completed' => false]);
        Task::factory()->count(4)->create(['user_id' => $user->id, 'progress' => 80, 'is_completed' => false]);
        Task::factory()->count(5)->create(['user_id' => $user->id, 'progress' => 100, 'is_completed' => true]);

        $response = $this->actingAs($user)
            ->get("/api/user/analysis?statistics=task_distribution_by_progress&time_range=all");

        $response->assertStatus(200)
            ->assertJson([
                'series' => [1, 2, 3, 4, 5],
                'labels' => ['Less than 25%', 'From 25% to 50%', 'From 51% to 75%', 'More than 75%', 'Completed']
            ]);
    }
    public function test_user_tasks_analysis_task_distribution_by_priority()
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $user->id, 'priority' => 'Normal']);
        Task::factory()->count(2)->create(['user_id' => $user->id, 'priority' => 'Important']);
        Task::factory()->count(1)->create(['user_id' => $user->id, 'priority' => 'Very Important']);

        $response = $this->actingAs($user)
            ->get("/api/user/analysis?statistics=task_distribution_by_priority&time_range=all");

        $response->assertStatus(200)
            ->assertJson([
                'series' => [3, 2, 1],
                'labels' => ['Normal', 'Important', 'Very Important']
            ]);
    }
}
