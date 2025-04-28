<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'user_id',
        'photo',
        'nip',
        'entry_level',
        'initial',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'age',
        'address',
        'province_id',
        'city_id',
        'district_id',
        'temporary_address',
        'email',
        'home_phone',
        'mobile_phone',
        'fax',
        'ktp',
        'npwp',
        'department_id',
        'bank_id',
        'account_number',
        'jamsostek',
        'dplk',
        'inhealth',
        'religion_id',
        'employee_type_id',
        'grade',
        'functional_position_id',
        'structural_position_id',
        'sub_department_id',
        'eselon_id',
        'marital_status',
        'employee_status',
        'join_date',
        'contract_end_date',
        'education',
        'position_date',
        'position_id',
        'status',
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data tertentu
     */
    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'contract_end_date' => 'date',
        'position_date' => 'date',
    ];

    /**
     * Mendapatkan user yang terkait dengan karyawan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan provinsi tempat tinggal karyawan
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Mendapatkan kota tempat tinggal karyawan
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Mendapatkan kecamatan tempat tinggal karyawan
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Mendapatkan departemen karyawan
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(RefBagian::class, 'department_id');
    }

    /**
     * Mendapatkan sub departemen karyawan
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(RefSubBagian::class, 'sub_department_id');
    }

    /**
     * Mendapatkan bank karyawan
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Mendapatkan agama karyawan
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(RefAgama::class, 'religion_id');
    }

    /**
     * Mendapatkan jenis pegawai
     */
    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(RefStatusPegawai::class, 'employee_type_id');
    }

    /**
     * Mendapatkan jabatan fungsional
     */
    public function functionalPosition(): BelongsTo
    {
        return $this->belongsTo(RefJabatanFungsional::class, 'functional_position_id');
    }

    /**
     * Mendapatkan jabatan struktural
     */
    public function structuralPosition(): BelongsTo
    {
        return $this->belongsTo(RefJabatanStruktural::class, 'structural_position_id');
    }

    /**
     * Mendapatkan eselon
     */
    public function eselon(): BelongsTo
    {
        return $this->belongsTo(RefEselon::class, 'eselon_id');
    }

    /**
     * Mendapatkan jabatan
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(RefJabatan::class, 'position_id');
    }
} 