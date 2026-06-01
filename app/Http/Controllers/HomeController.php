<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Staff;
use App\Models\Student;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Beranda', [
            'jumlahBuku' => Book::count(),
            'jumlahSiswa' => Student::count(),
            'jumlahKaryawan' => Staff::count(),

            'bukuDipinjam' => Loan::with(['loanable:id,nama', 'book:id,judul'])
                ->select('loanable_type', 'loanable_id', 'book_id')
                ->latest()
                ->limit(6)
                ->get(),

            'peminjamTerbanyak' => Loan::with(['loanable:id,nama'])
                ->select('loanable_type', 'loanable_id')
                ->selectRaw('COUNT(id) as total')
                ->groupBy(['loanable_type', 'loanable_id'])
                ->orderByDesc('total')
                ->limit(6)
                ->get(),
        ]);
    }
}
