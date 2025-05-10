<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Mendapatkan semua karyawan di departemen ini
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
} 