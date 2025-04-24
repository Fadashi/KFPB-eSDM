<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefShift extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_shift';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'shift',
        'jam_masuk',
        'jam_keluar',
        'latitude',
        'longitude',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
}
