<?php

namespace App\Exports;

use App\Models\SubscriptionPackage;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubscriptionPackageExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private $no = 0;

    public function headings(): array
    {
        return [
            // Baris 1: Judul Laporan (Akan berada di cell A1)
            ['Data Paket Langganan dengan Buku dan Pengguna'],

            // Baris 2: Header Tabel (Akan berada di baris ke-2)
            [
                'Nomor',
                'Nama Paket Langganan',
                'Harga',
                'Jumlah Buku',
                'Pelanggan Aktif',
            ],
        ];
    }

    public function collection(): Collection
    {
        // Load kedua relasi & filter pelanggan aktif (expired belum melewati hari ini)
        return SubscriptionPackage::with([
            'books',
            'subscriptionPackageUsers' => function ($query) {
                $query->where('expired_date', '>=', now()->toDateString());
            },
        ])->get();
    }

    public function map($model): array
    {
        return [
            ++$this->no, // post-increment : nambahin sblm dimunculin
            $model->name_package,
            'Rp '.number_format($model->price, 0, ',', '.'),
            $model->books->count(),
            $model->subscriptionPackageUsers->count(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {

        $sheet->mergeCells('A1:E1');

        $totalRows = $this->collection()->count() + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],

            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'D3D3D3'], // Abu-abu
                ],
            ],

            "A2:E$totalRows" => [
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
