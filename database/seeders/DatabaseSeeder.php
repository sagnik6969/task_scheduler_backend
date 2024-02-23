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
        $users = User::factory()->count(5)->create();


        // Create 10 tasks associated with 5 users randomly
        $users->each(function ($user) {
            Task::factory()->count(2)->create(['user_id' => $user->id]);
        });
    }
}
