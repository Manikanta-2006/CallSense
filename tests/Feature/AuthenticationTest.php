<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the login page renders successfully.
     */
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Enter the');
        $response->assertSee('Command Center');
        $response->assertSee('Join the');
        $response->assertSee('Squad');
    }

    /**
     * Test user can register via credentials.
     */
    public function test_user_can_register_via_credentials(): void
    {
        $response = $this->post('/register', [
            'name' => 'Agent Kaito',
            'email' => 'kaito@callsense.io',
            'password' => 'secretagent123',
            'password_confirmation' => 'secretagent123',
        ]);

        $response->assertRedirect('/calls');
        $this->assertAuthenticated();
        
        $this->assertDatabaseHas('users', [
            'name' => 'Agent Kaito',
            'email' => 'kaito@callsense.io',
        ]);

        // Assert login activity logged
        $user = User::where('email', 'kaito@callsense.io')->first();
        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
    }

    /**
     * Test registration validation fails with mismatched passwords.
     */
    public function test_user_cannot_register_with_mismatched_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Agent Kaito',
            'email' => 'kaito@callsense.io',
            'password' => 'secretagent123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * Test user can login with correct credentials.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::create([
            'name' => 'Agent Ryan',
            'email' => 'ryan@callsense.io',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ryan@callsense.io',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/calls');
        $this->assertAuthenticatedAs($user);

        // Assert login activity logged
        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
    }

    /**
     * Test user cannot login with incorrect password.
     */
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::create([
            'name' => 'Agent Ryan',
            'email' => 'ryan@callsense.io',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ryan@callsense.io',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
