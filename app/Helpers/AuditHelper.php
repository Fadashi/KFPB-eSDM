<?php

namespace App\Helpers;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AuditHelper
{
    /**
     * Catat aktivitas audit.
     *
     * @param string $aksi      Aksi yang dilakukan (tambah, ubah, hapus)
     * @param string $model     Nama model (misal: Pegawai, Pengumuman)
     * @param string $data      Keterangan data (misal: "Nama Pegawai")
     * @param mixed  $valueAsal Nilai sebelum perubahan
     * @param mixed  $valueBaru Nilai sesudah perubahan
     */
    public static function log(string $aksi, string $model, string $data, $valueAsal = null, $valueBaru = null)
    {
        AuditTrail::create([
            'user_id'    => Auth::id(),
            'aksi'       => $aksi,
            'model'      => $model,
            'data'       => $data,
            'value_asal' => $valueAsal ? json_encode($valueAsal) : null,
            'value_baru' => $valueBaru ? json_encode($valueBaru) : null,
        ]);
    }
} 