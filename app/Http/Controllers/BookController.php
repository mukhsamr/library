<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Traits\HasTryCatch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookController extends Controller
{
    use HasTryCatch;

    public function daftar(Request $request)
    {
        $kolom = [
            'judul' => 'Judul',
            'nik' => 'NIK',
            'pengarang' => 'Pengarang',
            'penerbit' => 'Penerbit',
            'isbn' => 'ISBN'
        ];

        return Inertia::render('Buku/Daftar', [
            'kolom' => $kolom,
            'buku' => Book::when($request->kategori)
                ->where($request->kategori, 'like', "%$request->keyword%")
                ->withCount('activeLoans as loans_count')
                ->orderBy('nik')
                ->paginate(10)
                ->onEachSide(1)
                ->withQueryString()
                ->through(function ($query) {
                    $query->append(['dibuat', 'diedit']);
                    return $query;
                }),

            'kategori' => $request->kategori ?? array_key_first($kolom),
            'keyword' => $request->keyword,
        ]);
    }

    public function detail(Request $request)
    {
        $buku = Book::select('nik', 'judul')->get();
        $detail = Book::withCount('activeLoans as loans_count')->when($request->nik)->where('nik', $request->nik)->first();
        $nik = $request->nik ?? $detail->nik ?? null;

        return Inertia::render('Buku/Detail', [
            'buku' => $buku,
            'detail' => $detail,
            'nik' => $nik,
            'riwayat' => Loan::with('loanable:id,nama')
                ->whereRelation('book', 'nik', $nik)
                ->limit(10)
                ->latest('created_at')
                ->get()
                ->each
                ->append('dibuat'),
        ]);
    }
}
