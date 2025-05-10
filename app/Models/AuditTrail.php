<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AuditTrail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'aksi',
        'model',
        'data',
        'value_asal',
        'value_baru',
    ];

    /**
     * Get the user that owns the audit entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
