<?php

namespace App\Exports;

use App\Models\Book;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $no = 0;

    public function headings(): array
    {
        return [
            'No',
            'Alamat Gambar Sampul Buku',
            'Nama Kategori Buku',
            'Judul Buku',
            'Penulis',
            'Penerbit',
            'Tanggal Rilis',
            'Jumlah Halaman',
            'Bahasa',
            'Harga',
        ];
    }

    public function collection(): Collection
    {
        return Book::with('bookCategory')->get();
    }

    public function map($book): array
    {
        return [
            ++$this->no, // post-increment : nambahin sblm dimunculin
            $book->cover,
            $book->bookCategory->name,
            $book->title,
            $book->writer,
            $book->publisher,
            $book->release_date = date('d F Y'),
            $book->page_of_book,
            $book->language,
            'Rp '.number_format($book->price, 0, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // total jumlah baris, untuk batas style border
        $totalRows = $this->collection()->count() + 1;

        return [
            // Style the first row as bold text.
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'D3D3D3'], // Abu-abu
                ],
            ],

            "A1:J$totalRows" => [
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
