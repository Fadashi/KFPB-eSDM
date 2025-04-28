<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'city_id',
        'name',
    ];

    /**
     * Mendapatkan kota tempat kecamatan ini berada
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Mendapatkan semua karyawan yang tinggal di kecamatan ini
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
} 