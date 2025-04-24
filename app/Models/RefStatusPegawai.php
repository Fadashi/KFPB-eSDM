<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefStatusPegawai extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_status_pegawai';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'status_pegawai',
    ];
} 