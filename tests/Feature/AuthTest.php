<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_a_user_can_sign_up()
    {
        $response = $this->postJson('/api/register', [
            "name" => "sagnik",
            "email" => "userid@gmail.com",
            "password" => "12345678",
            "password_confirmation" => "12345678"
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            "name" => "sagnik",
            "email" => "userid@gmail.com",
        ]);
    }

    public static function invalidUserProvider()
    {
        $user =
            [
                "name" => "sagnik",
                "email" => "userid@gmail.com",
                "password" => "12345678",
                "password_confirmation" => "12345678"
            ]
        ;

        $users = [];

        foreach ($user as $key => $value) {
            $userCopy = $user;
            $userCopy[$key] = null;
            $users["when {$key} is empty"] = [$userCopy];
        }
        return $users;
    }

    /**
     * @dataProvider invalidUserProvider
     */
    public function test_validation_errors_in_signup_route($user)
    {
        $response = $this->postJson('/api/register', $user);
        $response->assertStatus(422);
    }


    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('12345678')
        ]);

        $response = $this->postJson('/api/login', [
            "email" => $user->email,
            "password" => '12345678'
        ]);

        $response->assertStatus(200);

    }

    public function test_users_with_invalid_credentials_cant_login()
    {
        $response = $this->postJson('/api/login', [
            "email" => 'abc@d.com',
            "password" => '12345678'
        ]);

        $response->assertStatus(422);

    }



    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'password' => Hash::make('12345678')
        ]);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/logout');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_users_cant_logout()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }

}
