<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefAgama extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini
     */
    protected $table = 'ref_agama';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'agama',
    ];
}
