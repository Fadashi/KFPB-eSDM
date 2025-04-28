<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'province_id',
        'name',
        'type',
    ];

    /**
     * Mendapatkan provinsi tempat kota ini berada
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Mendapatkan semua kecamatan yang berada di kota ini
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * Mendapatkan semua karyawan yang tinggal di kota ini
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
} 