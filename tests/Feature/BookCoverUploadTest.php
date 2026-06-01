<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookCoverUploadTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'nama' => 'Admin Test',
            'password' => 'secret',
            'level' => 3,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'tanggal' => '2026-01-01',
            'nik' => 'BK001',
            'judul' => 'Buku Upload Test',
            'jumlah_buku' => 1,
        ], $overrides);
    }

    public function test_user_can_store_book_cover_on_public_disk()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user())->post(route('form.store'), $this->payload([
            'sampul' => UploadedFile::fake()->image('cover.jpg', 300, 300)->size(200),
        ]));

        $response->assertSessionHas('alert.status', 'success');
        Storage::disk('public')->assertExists('sampul/bk001.jpg');
        $this->assertDatabaseHas('books', [
            'nik' => 'BK001',
            'sampul' => 'bk001.jpg',
        ]);
    }

    public function test_book_cover_must_be_an_allowed_image()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user())->from(route('form.tambah'))->post(route('form.store'), $this->payload([
            'sampul' => UploadedFile::fake()->create('cover.pdf', 200, 'application/pdf'),
        ]));

        $response->assertSessionHasErrors('sampul');
        Storage::disk('public')->assertMissing('sampul/bk001.pdf');
        $this->assertDatabaseCount('books', 0);
    }

    public function test_user_can_replace_book_cover_and_old_cover_is_deleted()
    {
        Storage::fake('public');
        Storage::disk('public')->put('sampul/old.jpg', 'old cover');

        $book = Book::create($this->payload([
            'sampul' => 'old.jpg',
        ]));

        $response = $this->actingAs($this->user())->patch(route('form.update'), $this->payload([
            'id' => $book->id,
            'sampul' => UploadedFile::fake()->image('new-cover.png', 300, 300)->size(200),
        ]));

        $response->assertSessionHas('alert.status', 'success');
        Storage::disk('public')->assertMissing('sampul/old.jpg');
        Storage::disk('public')->assertExists('sampul/bk001.png');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'sampul' => 'bk001.png',
        ]);
    }
}
