<?php

namespace App\Http\Controllers;

use App\Exports\LoanExport;
use App\Http\Requests\LoanRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Staff;
use App\Models\Student;
use App\Traits\HasTryCatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class LoanController extends Controller
{
    use HasTryCatch;

    public function form(Request $request)
    {
        $student = Student::has('grade')->select('nis', 'nama')->get();
        $staff = Staff::has('unit')->select('nik', 'nama')->get();

        return Inertia::render('Peminjaman/Form', [
            'listBuku' => Book::select('nik', 'judul')->get(),
            'nikBuku' => $request->nikBuku,
            'buku' => Book::select('id', 'judul', 'nik', 'pengarang', 'penerbit', 'jumlah_buku', 'sampul')
                ->withCount('activeLoans as dipinjam')
                ->firstWhere('nik', $request->nikBuku),

            'listUser' => $student->concat($staff),
        ]);
    }

    public function riwayat(Request $request)
    {
        $riwayat = Loan::with(['loanable', 'book:id,nik,judul'])
            ->whenStatus($request->status)

            // Cari buku / peminjam
            ->when($request->keyword, function ($query) use ($request) {

                // Buku
                if ($request->kategori == 'buku') {
                    $query->whereHas(
                        'book',
                        fn ($query) => $query->where('judul', 'like', "%$request->keyword%")
                            ->orWhere('nik', 'like', "%$request->keyword%")
                    );
                }

                // Peminjam
                if ($request->kategori == 'peminjam') {
                    $query->whereHas(
                        'loanable',
                        fn ($query) => $query->where('nama', 'like', "%$request->keyword%")
                    );
                }
            })

            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString()
            ->through(function ($item) {
                $item->append(['dibuat', 'dikembalikan_pada', 'status']);
                return $item;
            });


        return Inertia::render('Peminjaman/Riwayat', [
            'riwayat' => $riwayat,
            'status' => $request->status ?? '0',
            'kategori' => $request->kategori ?? 'buku',
            'keyword' => $request->keyword,
        ]);
    }

    // 

    public function store(LoanRequest $request)
    {
        $peminjam = $request->peminjam();

        if (!$peminjam) {
            throw ValidationException::withMessages([
                'user' => 'Peminjam tidak ditemukan.',
            ]);
        }

        $alert = $this::execute(
            try: function () use ($request, $peminjam) {
                DB::transaction(function () use ($request, $peminjam) {
                    $buku = Book::whereKey($request->buku)->lockForUpdate()->firstOrFail();
                    $dipinjam = Loan::where('book_id', $buku->id)->whereNull('returned_at')->count();

                    if ($buku->jumlah_buku <= $dipinjam) {
                        throw ValidationException::withMessages([
                            'buku' => 'Stok buku tidak tersedia.',
                        ]);
                    }

                    $peminjam->loans()->create([
                        'book_id' => $buku->id,
                        'dipinjam' => $request->dari,
                        'dikembalikan' => $request->sampai,
                        'catatan' => $request->catatan,
                    ]);
                });
            },
            message: 'pinjam buku'
        );

        return back()->with('alert', $alert);
    }

    public function selesai(Loan $loan)
    {
        $loan->load('book');
        $alert = $this::execute(
            try: fn () => $loan->update(['returned_at' => now()]),
            message: 'konfirmasi <li>' . $loan->book->judul . '</li>'
        );

        return back()->with('alert', $alert);
    }

    public function restore(Loan $loan)
    {
        $loan->load('book');
        $alert = $this::execute(
            try: fn () => $loan->update(['returned_at' => null]),
            message: 'batalkan <li>' . $loan->book->judul . '</li>'
        );

        return back()->with('alert', $alert);
    }

    public function destroy(Loan $loan)
    {
        $loan->load('book');
        $alert = $this::execute(
            try: fn () => $loan->forceDelete(),
            message: 'hapus riwayat <li>' . $loan->book->judul . '</li>'
        );

        return back()->with('alert', $alert);
    }

    public function update(Request $request)
    {
        $alert = $this::execute(
            try: fn () => Loan::where('id', $request->id)->update($request->only(['dipinjam', 'dikembalikan'])),
            message: 'edit riwayat'
        );

        return back()->with('alert', $alert);
    }

    public function export(Request $request)
    {
        $dari = Carbon::parse($request->dari)->translatedFormat('d M Y');
        $sampai = Carbon::parse($request->sampai)->translatedFormat('d M Y');

        $nama = "Riwayat Peminjaman $dari - $sampai.xlsx";

        return (new LoanExport($request))->download($nama);
    }
}
