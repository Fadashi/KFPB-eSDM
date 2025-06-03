<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Carbon\Carbon;
use App\Models\Department;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Debug: log row yang diimport
        \Log::info('IMPORT ROW:', $row);

        $departmentId = Department::where('name', $row['bagian'])->value('id');
        \Log::info('DEPARTMENT ID:', [$departmentId]);

        $employee = new \App\Models\Employee([
            'name' => $row['nama_karyawan'] ?? null,
            'nip' => $row['nip'] ?? null,
            'gender' => $row['jenis_kelamin'] ?? null,
            'user_id' => $row['user_id'] ?? null,
            'photo' => $row['photo'] ?? null,
            'entry_level' => $row['entry_level'] ?? null,
            'initial' => $row['initial'] ?? null,
            'birth_place' => $row['tempat_lahir'] ?? null,
            'birth_date' => isset($row['tanggal_lahir']) ? \Carbon\Carbon::parse($row['tanggal_lahir']) : null,
            'age' => $row['umur'] ?? null,
            'address' => $row['alamat'] ?? null,
            'province_id' => $row['provinsi_id'] ?? null,
            'city_id' => $row['kota_id'] ?? null,
            'district_id' => $row['kecamatan_id'] ?? null,
            'temporary_address' => $row['alamat_sementara'] ?? null,
            'email' => $row['email'] ?? null,
            'home_phone' => $row['no_telp_rumah'] ?? null,
            'mobile_phone' => $row['no_hp'] ?? null,
            'fax' => $row['no_fax'] ?? null,
            'ktp' => $row['ktp'] ?? null,
            'npwp' => $row['npwp'] ?? null,
            'department_id' => $departmentId,
            'bank_id' => $row['bank_id'] ?? null,
            'account_number' => $row['rekening'] ?? null,
            'jamsostek' => $row['no_jamsostek'] ?? null,
            'dplk' => $row['no_dplk'] ?? null,
            'inhealth' => $row['no_inhealth'] ?? null,
            'religion_id' => $row['agama_id'] ?? null,
            'employee_type_id' => $row['jenis_pegawai_id'] ?? null,
            'grade' => $row['eselon'] ?? null,
            'functional_position_id' => $row['jabatan_fungsional_id'] ?? null,
            'structural_position_id' => $row['jabatan_struktural_id'] ?? null,
            'sub_department_id' => $row['sub_bagian_id'] ?? null,
            'eselon_id' => $row['eselon'] ?? null,
            'marital_status' => $row['status_kawin'] ?? null,
            'employee_status' => $row['status_keaktifan'] ?? 'Aktif',
            'join_date' => isset($row['tanggal_masuk']) ? \Carbon\Carbon::parse($row['tanggal_masuk']) : null,
            'contract_end_date' => isset($row['tanggal_habis_kontrak']) ? \Carbon\Carbon::parse($row['tanggal_habis_kontrak']) : null,
            'education' => $row['pendidikan'] ?? null,
            'position' => $row['jabatan'] ?? null,
            'position_date' => isset($row['tanggal_jabatan']) ? \Carbon\Carbon::parse($row['tanggal_jabatan']) : null,
            'position_id' => $row['jabatan_id'] ?? null,
            'status' => $row['status_keaktifan'] ?? 'Aktif',
        ]);

        \Log::info('EMPLOYEE TO SAVE:', $employee->toArray());

        return $employee;
    }

    public function rules(): array
    {
        return [
            '*.nip' => 'required|string|unique:employees,nip',
            '*.nama_karyawan' => 'required|string',
            '*.jenis_kelamin' => ['required', \Illuminate\Validation\Rule::in(['L', 'P'])],
            '*.alamat' => 'required|string',
            '*.email' => 'required|email',
            '*.status_keaktifan' => ['nullable', \Illuminate\Validation\Rule::in(['Aktif','Non Aktif','Cuti'])],
            '*.status_kawin' => ['nullable', \Illuminate\Validation\Rule::in(['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'])],
            '*.education' => ['nullable', \Illuminate\Validation\Rule::in(['SD','SMP','SMA','D3','S1','S2','S3'])],
            '*.status' => ['nullable', \Illuminate\Validation\Rule::in(['Aktif','Non Aktif','Cuti'])],
            // Tambahkan validasi lain sesuai kebutuhan
        ];
    }
}
