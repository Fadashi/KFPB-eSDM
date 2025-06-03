<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Ambil semua data karyawan beserta relasi yang dibutuhkan
        return Employee::with([
            'department', 'subDepartment', 'employeeType', 'structuralPosition', 'functionalPosition'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'NIP',
            'Jenis Kelamin',
            'Status Kawin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'Email',
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

    public function map($employee): array
    {
        return [
            $employee->name,
            (string) $employee->nip,
            $employee->gender,
            $employee->marital_status,
            $employee->birth_place,
            $employee->birth_date ? date('Y-m-d', strtotime($employee->birth_date)) : '',
            $employee->address,
            $employee->email,
            (string) $employee->mobile_phone,
            (string) $employee->home_phone,
            (string) $employee->fax,
            (string) $employee->ktp,
            (string) $employee->npwp,
            optional($employee->bank)->nama_bank ?? '', // ganti sesuai relasi bank
            (string) $employee->account_number,
            optional($employee->department)->name ?? '',
            optional($employee->subDepartment)->subbagian ?? '',
            optional($employee->employeeType)->status_pegawai ?? '',
            optional($employee->structuralPosition)->jabatan_struktural ?? '',
            optional($employee->functionalPosition)->jabatan_fungsional ?? '',
            $employee->grade,
            (string) $employee->jamsostek,
            (string) $employee->dplk,
            (string) $employee->inhealth,
            $employee->join_date ? date('Y-m-d', strtotime($employee->join_date)) : '',
            $employee->contract_end_date ? date('Y-m-d', strtotime($employee->contract_end_date)) : '',
            optional($employee->position)->jabatan ?? '',
            $employee->position_date ? date('Y-m-d', strtotime($employee->position_date)) : '',
            $employee->status,
        ];
    }

    // Style heading
    public function styles(Worksheet $sheet)
    {
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
        // Lebar kolom otomatis
        for ($i = 1; $i <= count($this->headings()); $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return [];
    }

    // Format kolom nomor sebagai text
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $textColumns = ['B','I','J','K','L','O','V','W','X','M']; // NIP, No HP, Telp Rumah, Fax, KTP, Rekening, Jamsostek, DPLK, InHealth, NPWP
                foreach ($textColumns as $col) {
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
