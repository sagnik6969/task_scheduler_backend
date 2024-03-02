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
}
