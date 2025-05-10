<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Mendapatkan semua kota yang berada di provinsi ini
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Mendapatkan semua karyawan yang tinggal di provinsi ini
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
} 