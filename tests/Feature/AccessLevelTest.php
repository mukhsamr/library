<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_three_user_can_access_student_management()
    {
        $admin = User::create([
            'nama' => 'Admin Test',
            'password' => 'secret',
            'level' => 3,
        ]);

        $this->actingAs($admin)->get(route('siswa.index'))->assertOk();
    }

    public function test_low_level_user_cannot_access_student_management()
    {
        $user = User::create([
            'nama' => 'User Test',
            'password' => 'secret',
            'level' => 1,
        ]);

        $this->actingAs($user)->get(route('siswa.index'))->assertForbidden();
    }
}
