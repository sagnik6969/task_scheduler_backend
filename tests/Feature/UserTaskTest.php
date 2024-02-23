<?php

namespace Tests\Feature;

use App\Models\User;
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
            $user = User::find(2);

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

        
}
