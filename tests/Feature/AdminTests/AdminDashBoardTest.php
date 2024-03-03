<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminDashBoardTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    // public function test_unauthenticated_redirected_to_login()
    // {
    //     $response = $this->get('/admin');

    //     $response->assertStatus(302);

    //     $response->assertRedirect('/login');
    // }


    public function test_allows_authenticated_users_to_access_protected_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/api/admin');

        $response->assertStatus(302);

        $response->assertRedirect('/');
    }

    public function test_authenticated_admin_can_access_admin_page()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        $response = $this->get('/admin');

        $response->assertStatus(200);

    }

}
