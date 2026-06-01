<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileUploadTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $overrides = []): User
    {
        return User::create(array_merge([
            'nama' => 'Admin Test',
            'password' => 'secret',
            'level' => 3,
        ], $overrides));
    }

    public function test_user_can_update_profile_photo_on_public_disk()
    {
        Storage::fake('public');
        $user = $this->user();

        $response = $this->actingAs($user)->patch(route('user.update'), [
            'nama' => 'Admin Test',
            'foto' => UploadedFile::fake()->image('profile.jpg', 300, 300)->size(200),
        ]);

        $response->assertSessionHas('alert.status', 'success');
        Storage::disk('public')->assertExists('images/admin-test.jpg');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'foto' => 'admin-test.jpg',
        ]);
    }

    public function test_profile_photo_must_be_an_allowed_image()
    {
        Storage::fake('public');
        $user = $this->user();

        $response = $this->actingAs($user)->from(route('user.index'))->patch(route('user.update'), [
            'nama' => 'Admin Test',
            'foto' => UploadedFile::fake()->create('profile.pdf', 200, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('foto');
        Storage::disk('public')->assertMissing('images/admin-test.pdf');
    }

    public function test_replacing_profile_photo_deletes_old_photo()
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/old.jpg', 'old photo');
        $user = $this->user(['foto' => 'old.jpg']);

        $response = $this->actingAs($user)->patch(route('user.update'), [
            'nama' => 'Admin Test',
            'foto' => UploadedFile::fake()->image('new-profile.webp', 300, 300)->size(200),
        ]);

        $response->assertSessionHas('alert.status', 'success');
        Storage::disk('public')->assertMissing('images/old.jpg');
        Storage::disk('public')->assertExists('images/admin-test.webp');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'foto' => 'admin-test.webp',
        ]);
    }
}
