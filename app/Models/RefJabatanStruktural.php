<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefJabatanStruktural extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_jabatan_struktural';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'jabatan_struktural',
        'kelas',
    ];
} 