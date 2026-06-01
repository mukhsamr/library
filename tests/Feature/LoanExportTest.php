<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_loan_history()
    {
        $user = User::create([
            'nama' => 'Admin Test',
            'password' => 'secret',
            'level' => 3,
        ]);
        $grade = Grade::create(['kelas' => '1A']);
        $student = Student::create([
            'nama' => 'Siswa Test',
            'nis' => 'SIS001',
            'grade_id' => $grade->id,
        ]);
        $book = Book::create([
            'tanggal' => '2026-01-01',
            'nik' => 'BK001',
            'judul' => 'Buku Export Test',
            'jumlah_buku' => 1,
        ]);
        $student->loans()->create([
            'book_id' => $book->id,
            'dipinjam' => '2026-01-01',
            'dikembalikan' => '2026-01-07',
        ]);

        $response = $this->actingAs($user)->get(route('peminjaman.export', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }
}
