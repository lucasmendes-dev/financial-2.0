<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'P@ssword123',
            'password_confirmation' => 'P@ssword123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'access_token',
                    'token_type'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('P@ssword123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'P@ssword123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'user' => ['id', 'name', 'email'],
                         'access_token',
                         'token_type'
                     ]
                 ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out successfully']);

        $this->assertCount(0, $user->tokens);
    }

    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'user',
                         'new_token',
                         'token_type'
                     ]
                 ]);

        $this->assertCount(1, $user->tokens);
    }

    public function test_user_can_get_their_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'You are already logged in',
                     'data' => [
                         'user' => [
                             'id' => $user->id,
                             'email' => $user->email,
                         ]
                     ]
                 ]);
    }
}
