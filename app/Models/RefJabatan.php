<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefJabatan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_jabatan';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'kode',
        'jabatan',
        'gaji_pokok',
        'tunjangan',
        'masa',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'masa' => 'decimal:2',
    ];
}
