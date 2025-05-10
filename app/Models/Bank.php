<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Mendapatkan semua karyawan yang menggunakan bank ini
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
} 