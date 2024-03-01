<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTaskTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    // public function test_example(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    public function test_can_create_a_task()
    {
        // Create a user
        $user = User::factory()->create();

        // Make a POST request to the create endpoint
        $response = $this->actingAs($user)->post('/api/user/tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task.',
            'deadline' => '2024-02-29 12:00:00',
            'is_completed' => false,
            'progress' => 0,
            'priority' => 'normal',
        ]);

        // Assert that the request was successful (HTTP status code 201)
        $response->assertStatus(201);

        // Assert that the task exists in the database
        // $this->assertDatabaseHas('tasks', [
        //     'title' => 'Test Task',
        //     'description' => 'This is a test task.',
        //     'deadline' => '2024-02-29 12:00:00',
        //     'is_completed' => false,
        //     'progress' => 0,
        //     'priority' => 'normal',
        //     'user_id' => $user->id,
        // ]);

        // Assert that the response contains the created task data
        $response->assertJson([
            'data' => [
                'type' => 'create',
                'attributes' => [
                    'title' => 'Test Task',
                    'description' => 'This is a test task.',
                    'deadline' => '2024-02-29 12:00:00',
                    'is_completed' => false,
                    'progress' => 0,
                    'priority' => 'normal',
                    'user_id' => $user->id,
                ],
            ],
        ]);
    }


    public function test_can_retrieve_all_tasks_of_a_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create some tasks for the user
        $tasks = Task::factory(3)->create(['user_id' => $user->id]);

        // Make a GET request to retrieve all tasks for the user
        $response = $this->actingAs($user)->get('/api/user/tasks');

        // Assert that the request was successful (HTTP status code 200)
        $response->assertStatus(200);

        // // Assert that the response contains the tasks data
        // $response->assertJson([
        //     'data' => $tasks->toArray(),
        // ]);
    }

    public function test_can_retrieve_a_specific_task_of_a_user()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/api/user/tasks/' . $task->id);

        $response->assertStatus(200);

        // // Assert that the response contains the task data
        // $response->assertJson([
        //     'data' => $task->toArray(),
        // ]);
    }

    // public function test_can_update_a_task()
    // {
    //     // Create a user
    //     $user = User::factory()->create();

    //     // Create a task for the user
    //     $task = Task::factory()->create(['user_id' => $user->id]);

    //     // Make a PUT request to update the task
    //     $response = $this->actingAs($user)->get('/api/user/tasks/' . $task->id, [
    //         'title' => 'Updated Task Title',
    //         'description' => 'Updated task description.',
    //         'deadline' => '2024-03-31 12:00:00',
    //         'is_completed' => true,
    //         'progress' => 100,
    //         'priority' => 'high',
    //     ]);

    //     // Assert that the request was successful (HTTP status code 200)
    //     $response->assertStatus(200);

    //     // Assert that the task in the database has been updated
    //     $this->assertDatabaseHas('tasks', [
    //         'id' => $task->id,
    //         'title' => 'Updated Task Title',
    //         'description' => 'Updated task description.',
    //         'deadline' => '2024-03-31 12:00:00',
    //         'is_completed' => true,
    //         'progress' => 100,
    //         'priority' => 'high',
    //     ]);
    // }

    public function test_can_delete_a_task()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete('/api/user/tasks/' . $task->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
