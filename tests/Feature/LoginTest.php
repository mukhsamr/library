<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::create([
            'nama' => 'Admin',
            'password' => 'secret',
            'level' => 3,
        ]);

        $response = $this->post('/login', [
            'nama' => 'Admin',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        User::create([
            'nama' => 'Admin',
            'password' => 'secret',
            'level' => 3,
        ]);

        $response = $this->from('/login')->post('/login', [
            'nama' => 'Admin',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('fail');
        $this->assertGuest();
    }
}
