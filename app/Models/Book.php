<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Milon\Barcode\DNS1D;

class Book extends Model
{
    use HasFactory,
        SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];


    // Casting 
    protected function barcode(): Attribute
    {
        return Attribute::make(
            get: fn () => DNS1D::getBarcodeSVG($this->nik, 'C128', 1, 33, null, false),
        );
    }

    protected function dibuat(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'content' => $this->created_at
                    ? Carbon::parse($this->created_at)->translatedFormat('d M Y')
                    : null,
                'tooltips' => $this->created_at?->format('H:i:s')
            ],
        );
    }

    protected function diedit(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'content' => $this->updated_at
                    ? Carbon::parse($this->updated_at)->translatedFormat('d M Y')
                    : null,
                'tooltips' => $this->updated_at?->format('H:i:s')
            ],
        );
    }

    protected function dihapus(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'content' => $this->deleted_at
                    ? Carbon::parse($this->deleted_at)->translatedFormat('d M Y')
                    : null,
                'tooltips' => $this->deleted_at?->format('H:i:s')
            ],
        );
    }

    protected function pengarangKode(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper(substr($this->pengarang, 0, 3)) ?: null,
        );
    }

    protected function judulKode(): Attribute
    {
        $short = '';
        foreach (str_split($this->judul) as $v) {
            if (ctype_alpha($v) && !is_numeric($v)) {
                $short = $v;
                break;
            }
        }

        return Attribute::make(
            get: fn () => strtolower($short) ?: null,
        );
    }

    protected function jenisWarna(): Attribute
    {
        $type = [
            'English' => '#9BC2E6',
            'Arab' => '#0070C0',
            'Dewasa' => '#FFC000',
            'Anak Besar' => '#F4B084',
            'Anak' => '#548235',
            'Saku' => '#FFFFFF',
            'Referensi' => '#FF66FF'
        ];

        return Attribute::make(
            get: fn () => $type[trim(explode('-', ucwords($this->jenis))[0])] ?? '#000000',
        );
    }

    // Relations
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans()
    {
        return $this->hasMany(Loan::class)->whereNull('returned_at');
    }
}
