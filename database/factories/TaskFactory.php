<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // protected $model = Task::class;

    public function definition()
    {
        return [
            'user_id' => rand(1, 5), 
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'deadline' => $this->faker->dateTimeThisMonth,
            'is_completed' => $this->faker->boolean(), 
            'progress' => $this->faker->numberBetween(0, 100),      
            'priority' => $this->faker->randomElement(Task::$priorities),
        ];
    } 
}
