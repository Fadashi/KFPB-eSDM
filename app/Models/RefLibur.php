<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefLibur extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_libur';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'nama_libur',
        'tanggal_libur',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'tanggal_libur' => 'date',
    ];
} 