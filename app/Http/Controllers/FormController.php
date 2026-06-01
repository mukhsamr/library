<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Imports\BookImport;
use App\Models\Book;
use App\Traits\HasTryCatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Inertia\Inertia;

class FormController extends Controller
{
    use HasTryCatch;

    public function upload()
    {
        return Inertia::render('Form/Upload');
    }

    public function tambah()
    {
        return Inertia::render('Form/Tambah');
    }

    public function edit(Request $request)
    {
        return Inertia::render('Form/Edit', [
            'listBuku' => Book::select('nik', 'judul')->get(),
            'nikBuku' => $request->nik,
            'buku' => Book::firstWhere('nik', $request->nik)
        ]);
    }

    public function hapus(Request $request)
    {
        return Inertia::render('Form/Hapus', [
            'listBuku' => Book::select('nik', 'judul', 'sampul')->get(),
            'nikBuku' => $request->nik,
            'buku' => Book::firstWhere('nik', $request->nik)
        ]);
    }

    public function trash()
    {
        return Inertia::render('Form/Trash', [
            'buku' => Book::select('id', 'nik', 'judul', 'catatan', 'deleted_at')
                ->onlyTrashed()
                ->latest('deleted_at')
                ->get()
                ->each
                ->append('dihapus')
        ]);
    }

    // 

    public function store(BookRequest $request)
    {
        $alert = $this::execute(
            try: function () use ($request) {
                $data = $request->validated();

                // Cek jika ada sampul
                if ($file = $request->file('sampul')) {
                    $name = str($request->nik)->slug()->append('.' . $file->extension());
                    Storage::disk('public')->makeDirectory('sampul');
                    Image::make($file)->save(Storage::disk('public')->path('sampul/' . $name->value()));

                    $data['sampul'] = $name->value();
                }

                Book::create($data);
            },
            message: "tambah buku <li>$request->judul</li>"
        );

        return back()->with('alert', $alert);
    }

    public function update(BookRequest $request)
    {
        $alert = $this::execute(
            try: function () use ($request) {
                $book = Book::findOrFail($request->id);
                $update = $request->validated();

                // Cek jika ada sampul
                if ($file = $request->file('sampul')) {
                    $name = str($request->nik)->slug()->append('.' . $file->extension());
                    Storage::disk('public')->makeDirectory('sampul');
                    Image::make($file)->save(Storage::disk('public')->path('sampul/' . $name->value()));

                    if ($book->sampul && $book->sampul !== $name->value()) {
                        Storage::disk('public')->delete('sampul/' . $book->sampul);
                    }

                    $update['sampul'] = $name->value();
                } else {
                    unset($update['sampul']);
                }

                $book->update($update);
            },
            message: "edit buku <li>$request->judul</li>"
        );

        return back()->with('alert', $alert);
    }

    public function checkUpload(Request $request)
    {
        $collection = (new BookImport)->toCollection($request->file('excel'));

        $listNik = $collection[0]->pluck('nik');

        $buku = Book::whereIn('nik', $listNik)->select('nik', 'judul')->get();
        $ditambah = $listNik->diff($buku->pluck('nik'));

        return back()->with('flash', [
            'ditambah' => $collection[0]->whereIn('nik', $ditambah)->values(),
            'diupdate' => $buku
        ]);
    }

    public function import(Request $request)
    {
        $import = new BookImport;
        $import->import($request->file('excel'));

        $fail = [];
        foreach ($import->failures() as $failure) {
            $fail[] = 'Baris ' . $failure->row() . ', ' .  implode(', ', $failure->errors());
        }

        $alert = $fail
            ?
            [
                'status' => 'warning',
                'msg' => 'periksa kembali file excel'
            ]
            :
            [
                'status' => 'success',
                'msg' => 'upload buku'
            ];

        if ($fail) session()->flash('flash.fail', $fail);

        return back()->with('alert', $alert);
    }

    public function remove(Request $request, Book $buku)
    {
        $alert = $this::execute(
            try: function () use ($buku, $request) {
                $buku->update(['catatan' => $request->catatan]);
                $buku->delete();
            },
            message: "hapus buku <li>$buku->judul</li>"
        );

        return back()->with('alert', $alert);
    }

    public function restore(Book $buku)
    {
        $alert = $this::execute(
            try: fn () => $buku->restore(),
            message: "pulihkan buku <li>$buku->judul</li>"
        );
        return back()->with('alert', $alert);
    }

    public function destroy(Book $buku)
    {
        $alert = $this::execute(
            try: fn () => $buku->forceDelete(),
            message: "hapus permanen buku <li>$buku->judul</li>"
        );

        return back()->with('alert', $alert);
    }
}
