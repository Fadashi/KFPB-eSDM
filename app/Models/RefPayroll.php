<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefPayroll extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_payroll';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'kode',
        'payroll',
        'penambah',
        'pengurang',
        'keterangan',
    ];
} 