<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BookImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_import_books_from_excel_file()
    {
        $user = User::create([
            'nama' => 'Admin Test',
            'password' => 'secret',
            'level' => 3,
        ]);

        $file = $this->excelFile();

        $response = $this->actingAs($user)->post(route('form.import'), [
            'excel' => $file,
        ]);

        $response->assertSessionHas('alert.status', 'success');
        $this->assertDatabaseHas('books', [
            'nik' => 'BKIMPORT001',
            'judul' => 'Buku Import Test',
            'jumlah_buku' => 2,
        ]);
    }

    private function excelFile(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headings = [
            'tanggal',
            'nik',
            'judul',
            'pengarang',
            'kota_penerbit',
            'penerbit',
            'edisi_cetakan',
            'tahun_terbit',
            'isbn',
            'sumber',
            'klasifikasi',
            'lokasi_penyimpanan',
            'jenis',
            'jumlah_halaman',
            'jumlah_buku',
            'deskripsi',
        ];
        $values = [
            46023,
            'BKIMPORT001',
            'Buku Import Test',
            'Pengarang Test',
            'Jakarta',
            'Penerbit Test',
            '1',
            2026,
            '9780000000001',
            'Beli',
            '000',
            'Rak A',
            'Anak',
            120,
            2,
            'Deskripsi Test',
        ];

        foreach ($headings as $index => $heading) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $heading);
            $sheet->setCellValueByColumnAndRow($index + 1, 2, $values[$index]);
        }

        $path = tempnam(sys_get_temp_dir(), 'books') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'books.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
