<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory()->count(5)->create()->each(fn($user) => Task::factory(10)->create([
            'user_id' => $user->id
        ]));




        // // Create 10 tasks associated with 5 users randomly
        // Task::factory(10)->create([
        //     'user_id' => 1,
        //     'created_at' => now()
        // ]);
        // Task::factory(10)->create([
        //     'user_id' => 1,
        //     'created_at' => now()->subHour()
        // ]);
        // Task::factory(10)->create([
        //     'user_id' => 1,
        //     'created_at' => now()->subDay()
        // ]);
        // Task::factory(10)->create([
        //     'user_id' => 1,
        //     'created_at' => now()->subMonth()
        // ]);
        // Task::factory(10)->create([
        //     'user_id' => 1,
        //     'created_at' => now()->subMonths(10)
        // ]);
    }
}
