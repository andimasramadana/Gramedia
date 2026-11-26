<?php

namespace App\Exports;

use App\Models\BookCategory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookCategoriesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $no = 0;

    public function headings(): array
    {
        return [
            'No',
            'Nama Kategori',
            'Jumlah Buku',
        ];
    }

    public function collection(): Collection
    {
        return BookCategory::with('books')->get();
    }

    public function map($bookCategory): array
    {
        return [
            ++$this->no, // post-increment : nambahin sblm dimunculin
            $bookCategory->name,
            $bookCategory->books->count(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // total jumlah baris, untuk batas style border
        $totalRows = $this->collection()->count() + 1;

        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],

            "A1:C$totalRows" => [
                'borders' => [
                    // atur border untuk semua, jika mau atas doang maka "top" dst
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'], // kode rgb hitam
                    ],
                ],
            ],
        ];
    }
}
