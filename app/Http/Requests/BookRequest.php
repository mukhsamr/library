<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nik' => 'required|string|unique:App\Models\Book,nik,' . $this->id,
            'judul' => 'required|string',
            'tanggal' => 'required|date',
            'pengarang' => 'string|nullable',
            'kota_penerbit' => 'string|nullable',
            'penerbit' => 'string|nullable',
            'edisi_cetakan' => 'string|nullable',
            'tahun_terbit' => 'integer|digits:4|nullable',
            'isbn' => 'string|nullable',
            'sumber' => 'string|nullable',
            'klasifikasi' => 'string|nullable',
            'lokasi_penyimpanan' => 'string|nullable',
            'jenis' => 'string|nullable',
            'jumlah_halaman' => 'integer|nullable',
            'jumlah_buku' => 'integer|nullable',
            'deskripsi' => 'string|nullable',
            'sampul' => 'file|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
