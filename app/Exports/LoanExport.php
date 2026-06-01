<?php

namespace App\Exports;

use App\Models\Loan;
use DateTime;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanExport extends StringValueBinder implements FromView, WithCustomValueBinder, WithStyles, ShouldAutoSize
{
    use Exportable;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        return view('exports.riwayat', [
            'riwayat' => Loan::has('loanable')
                ->with(['book', 'loanable'])
                ->whereDate('created_at', '>=', $this->request->dari)
                ->whereDate('created_at', '<=', $this->request->sampai)
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $highestCol = $sheet->getHighestColumn();

        $sheet->getStyle('A1:' . $highestCol . '1')->applyFromArray($styleArray);
    }
}
