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
    public function test_a_user_can_register()
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

    public function test_user_cannot_register_with_existing_email()
    {
        // Test registration with existing email
        $existingUser = User::factory()->create();
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => $existingUser->email,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }


    public static function invalidUserProvider()
    {
        $user =
            [
                "name" => "sagnik",
                "email" => "userid@gmail.com",
                "password" => "12345678",
                "password_confirmation" => "12345678"
            ];

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

    public function test_register_requires_credentials()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_requires_name()
    {
        // Test registration without name
        $response = $this->postJson('/api/register', [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_requires_email()
    {
        // Test registration without name
        $response = $this->postJson('/api/register', [
            'name' => 'name',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email_and_password()
    {
        // Test login without email and password
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // public function test_update_user_profile()
    // {
    //     // Test updating user profile
    //     $user = User::factory()->create();
    //     Sanctum::actingAs($user);

    //     $response = $this->putJson('/api/user', [
    //         'name' => 'Updated Name',
    //         'email' => 'updated@example.com',
    //     ]);

    //     $response->assertStatus(200);

    //     $this->assertDatabaseHas('users', [
    //         'id' => $user->id,
    //         'name' => 'Updated Name',
    //         'email' => 'updated@example.com',
    //     ]);
    // }

    // public function test_change_user_password()
    // {
    //     // Test changing user password
    //     $user = User::factory()->create();
    //     Sanctum::actingAs($user);

    //     $response = $this->putJson('/api/user/password', [
    //         'current_password' => 'password',
    //         'password' => 'newpassword',
    //         'password_confirmation' => 'newpassword',
    //     ]);

    //     $response->assertStatus(200);

    //     $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    // }

    // public function test_reset_user_password()
    // {
    //     // Test resetting user password
    //     $user = User::factory()->create();
    //     $token = Password::createToken($user);
    //     $response = $this->postJson('/api/reset-password', [
    //         'email' => $user->email,
    //         'token' => $token,
    //         'password' => 'newpassword',
    //         'password_confirmation' => 'newpassword',
    //     ]);

    //     $response->assertStatus(200);

    //     $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    // }
}
