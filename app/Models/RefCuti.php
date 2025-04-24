<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefCuti extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_cuti';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'nama_cuti',
        'jatah_cuti',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jatah_cuti' => 'integer',
    ];
} 