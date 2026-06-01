<?php

namespace App\Http\Requests;

use App\Models\Staff;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class LoanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user' => ['required', 'string'],
            'buku' => ['required', 'integer', 'exists:books,id'],
            'dari' => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function peminjam()
    {
        return Student::firstWhere('nis', $this->user)
            ?: Staff::firstWhere('nik', $this->user);
    }

}
