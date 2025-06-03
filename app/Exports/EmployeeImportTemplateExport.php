<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class EmployeeImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    public function array(): array
    {
        // Contoh baris data (opsional, bisa dikosongkan jika tidak ingin contoh)
        return [
            [
                'Nama Contoh', // Nama karyawan
                '123456789', // NIP
                'L', // Jenis Kelamin
                'Kawin', // Status kawin
                'Jakarta', // Tempat Lahir
                '1990-01-01', // Tanggal Lahir
                'Jl. Contoh No.1', // Alamat
                'email@contoh.com', // Email
                '08123456789', // No HP
                '0211234567', // No Telp Rumah
                '0217654321', // No Fax
                '0123456789012345', // KTP
                '09.123.456.7890.000', // NPWP
                'Bank BCA', // Bank
                '1234567890', // Rekening
                'Bagian A', // Bagian
                'Sub Bagian 1', // Sub Bagian
                'PNS', // Jenis Pegawai
                'Kepala Bagian', // Jabatan Struktural
                'Analis', // Jabatan Fungsional
                'III/a', // Eselon
                '1234567890', // No Jamsostek
                '1234567890', // No DPLK
                '1234567890', // No. InHealth
                '2020-01-01', // Tanggal Masuk
                '', // Tanggal Habis Kontrak
                'Staff', // Jabatan
                '', // Tanggal Jabatan
                'Aktif', // Status Keaktifan
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan*',
            'NIP*',
            'Jenis Kelamin*',
            'Status Kawin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat*',
            'Email*',
            'No HP',
            'No Telp Rumah',
            'No Fax',
            'KTP',
            'NPWP',
            'Bank',
            'Rekening',
            'Bagian',
            'Sub Bagian',
            'Jenis Pegawai',
            'Jabatan Struktural',
            'Jabatan Fungsional',
            'Eselon',
            'No Jamsostek',
            'No DPLK',
            'No. InHealth',
            'Tanggal Masuk',
            'Tanggal Habis Kontrak',
            'Jabatan',
            'Tanggal Jabatan',
            'Status Keaktifan',
        ];
    }

    // Membuat heading lebih bagus (bold, background)
    public function styles(Worksheet $sheet)
    {
        // Bold dan background untuk semua heading
        $sheet->getStyle('A1:AC1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'rgb' => 'D9E1F2'
                ]
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);
        // Kolom wajib diisi: A, B, C, G, H
        $requiredCols = ['A', 'B', 'C', 'G', 'H'];
        foreach ($requiredCols as $col) {
            $sheet->getStyle($col.'1')->applyFromArray([
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'rgb' => 'FFD966' // kuning
                    ]
                ],
                'font' => [
                    'color' => ['rgb' => 'C00000'], // merah
                ],
            ]);
        }
        // Lebar kolom otomatis
        for ($i = 1; $i <= count($this->headings()); $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Daftar tooltip untuk setiap kolom
                $tooltips = [
                    'A' => 'WAJIB DIISI. Nama lengkap karyawan',
                    'B' => 'WAJIB DIISI. NIP harus unik',
                    'C' => 'WAJIB DIISI. L (Laki-laki) / P (Perempuan)',
                    'D' => 'Status kawin: Kawin, Belum Kawin, Cerai Hidup, Cerai Mati',
                    'E' => 'Tempat lahir karyawan',
                    'F' => 'Tanggal lahir format YYYY-MM-DD',
                    'G' => 'WAJIB DIISI. Alamat lengkap',
                    'H' => 'WAJIB DIISI. Email aktif',
                    'I' => 'Nomor HP aktif',
                    'J' => 'Nomor telepon rumah (opsional)',
                    'K' => 'Nomor fax (opsional)',
                    'L' => 'Nomor KTP',
                    'M' => 'Nomor NPWP',
                    'N' => 'Nama bank (misal: BCA, BRI, Mandiri)',
                    'O' => 'Nomor rekening bank',
                    'P' => 'Nama bagian/departemen',
                    'Q' => 'Nama sub bagian (jika ada)',
                    'R' => 'Jenis pegawai: PNS, Honorer, dll',
                    'S' => 'Jabatan struktural (jika ada)',
                    'T' => 'Jabatan fungsional (jika ada)',
                    'U' => 'Eselon (misal: III/a, IV/b, dst)',
                    'V' => 'Nomor Jamsostek (opsional)',
                    'W' => 'Nomor DPLK (opsional)',
                    'X' => 'Nomor InHealth (opsional)',
                    'Y' => 'Tanggal masuk kerja format YYYY-MM-DD',
                    'Z' => 'Tanggal habis kontrak format YYYY-MM-DD (jika ada)',
                    'AA' => 'Jabatan (misal: Staff, Manager, dll)',
                    'AB' => 'Tanggal mulai jabatan format YYYY-MM-DD (jika ada)',
                    'AC' => 'Status keaktifan: Aktif, Non Aktif, Cuti',
                ];

                foreach ($tooltips as $col => $tip) {
                    $event->sheet->getDelegate()->getComment($col.'1')->getText()->createTextRun($tip);
                }

                // Kolom yang harus diformat text
                $textColumns = ['B','I','J','K','L','O','V','W','X','M']; // NIP, No HP, Telp Rumah, Fax, KTP, Rekening, Jamsostek, DPLK, InHealth, NPWP

                foreach ($textColumns as $col) {
                    for ($row = 2; $row <= 1000; $row++) {
                        $cell = $col . $row;
                        $event->sheet->getDelegate()->setCellValueExplicit($cell, $event->sheet->getDelegate()->getCell($cell)->getValue(), DataType::TYPE_STRING);
                    }
                    $event->sheet->getDelegate()->getStyle($col.'2:'.$col.'1000')
                        ->getNumberFormat()
                        ->setFormatCode('@');
                }

                // Kolom tanggal: F (Tanggal Lahir), Y (Tanggal Masuk), Z (Tanggal Habis Kontrak), AB (Tanggal Jabatan)
                $dateColumns = ['F','Y','Z','AB'];
                foreach ($dateColumns as $col) {
                    $event->sheet->getDelegate()->getStyle($col.'2:'.$col.'1000')
                        ->getNumberFormat()
                        ->setFormatCode('yyyy-mm-dd');
                }
            },
        ];
    }
}
