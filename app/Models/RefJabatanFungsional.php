<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefJabatanFungsional extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_jabatan_fungsional';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'jabatan_fungsional',
        'kelas',
        'umur_pensiun',
    ];
} 