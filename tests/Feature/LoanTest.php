<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Loan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'nama' => 'Admin Test',
            'password' => 'secret',
            'level' => 3,
        ]);
    }

    private function student(): Student
    {
        $grade = Grade::create(['kelas' => '1A']);

        return Student::create([
            'nama' => 'Siswa Test',
            'nis' => 'SIS001',
            'grade_id' => $grade->id,
        ]);
    }

    private function book(array $attributes = []): Book
    {
        return Book::create(array_merge([
            'tanggal' => '2026-01-01',
            'nik' => 'BK001',
            'judul' => 'Buku Test',
            'jumlah_buku' => 1,
        ], $attributes));
    }

    public function test_user_can_create_loan_when_stock_is_available()
    {
        $student = $this->student();
        $book = $this->book();

        $response = $this->actingAs($this->admin())->post(route('peminjaman.store'), [
            'user' => $student->nis,
            'buku' => $book->id,
            'dari' => '2026-01-01',
            'sampai' => '2026-01-07',
        ]);

        $response->assertSessionHas('alert.status', 'success');
        $this->assertDatabaseHas('loans', [
            'book_id' => $book->id,
            'loanable_id' => $student->id,
            'loanable_type' => Student::class,
        ]);
    }

    public function test_user_cannot_create_loan_when_stock_is_empty()
    {
        $student = $this->student();
        $book = $this->book(['jumlah_buku' => 1]);
        $student->loans()->create([
            'book_id' => $book->id,
            'dipinjam' => '2026-01-01',
            'dikembalikan' => '2026-01-07',
        ]);

        $response = $this->actingAs($this->admin())->from(route('peminjaman.form'))->post(route('peminjaman.store'), [
            'user' => $student->nis,
            'buku' => $book->id,
            'dari' => '2026-01-02',
            'sampai' => '2026-01-08',
        ]);

        $response->assertSessionHasErrors('buku');
        $this->assertSame(1, Loan::count());
    }


    public function test_user_cannot_create_loan_with_unknown_borrower()
    {
        $book = $this->book();

        $response = $this->actingAs($this->admin())->from(route('peminjaman.form'))->post(route('peminjaman.store'), [
            'user' => 'UNKNOWN',
            'buku' => $book->id,
            'dari' => '2026-01-01',
            'sampai' => '2026-01-07',
        ]);

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseCount('loans', 0);
    }

    public function test_user_cannot_create_loan_with_invalid_book_id()
    {
        $student = $this->student();

        $response = $this->actingAs($this->admin())->from(route('peminjaman.form'))->post(route('peminjaman.store'), [
            'user' => $student->nis,
            'buku' => 999,
            'dari' => '2026-01-01',
            'sampai' => '2026-01-07',
        ]);

        $response->assertSessionHasErrors('buku');
        $this->assertDatabaseCount('loans', 0);
    }


    public function test_user_can_mark_loan_as_returned_without_soft_deleting_history()
    {
        $student = $this->student();
        $book = $this->book();
        $loan = $student->loans()->create([
            'book_id' => $book->id,
            'dipinjam' => '2026-01-01',
            'dikembalikan' => '2026-01-07',
        ]);

        $response = $this->actingAs($this->admin())->delete(route('peminjaman.selesai', $loan));

        $response->assertSessionHas('alert.status', 'success');
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'deleted_at' => null,
        ]);
        $this->assertNotNull($loan->fresh()->returned_at);
    }

    public function test_user_can_cancel_returned_loan()
    {
        $student = $this->student();
        $book = $this->book();
        $loan = $student->loans()->create([
            'book_id' => $book->id,
            'dipinjam' => '2026-01-01',
            'dikembalikan' => '2026-01-07',
            'returned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())->patch(route('peminjaman.restore', $loan));

        $response->assertSessionHas('alert.status', 'success');
        $this->assertNull($loan->fresh()->returned_at);
    }

    public function test_returned_loan_does_not_reduce_available_stock()
    {
        $student = $this->student();
        $book = $this->book(['jumlah_buku' => 1]);
        $student->loans()->create([
            'book_id' => $book->id,
            'dipinjam' => '2026-01-01',
            'dikembalikan' => '2026-01-07',
            'returned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())->post(route('peminjaman.store'), [
            'user' => $student->nis,
            'buku' => $book->id,
            'dari' => '2026-01-08',
            'sampai' => '2026-01-15',
        ]);

        $response->assertSessionHas('alert.status', 'success');
        $this->assertSame(2, Loan::count());
    }

    public function test_return_date_must_not_be_before_loan_date()
    {
        $student = $this->student();
        $book = $this->book();

        $response = $this->actingAs($this->admin())->from(route('peminjaman.form'))->post(route('peminjaman.store'), [
            'user' => $student->nis,
            'buku' => $book->id,
            'dari' => '2026-01-07',
            'sampai' => '2026-01-01',
        ]);

        $response->assertSessionHasErrors('sampai');
        $this->assertDatabaseCount('loans', 0);
    }
}
