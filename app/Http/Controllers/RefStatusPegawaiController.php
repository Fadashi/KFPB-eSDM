<?php

namespace App\Http\Controllers;

use App\Models\RefStatusPegawai;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefStatusPegawaiController extends Controller
{
    /**
     * Menampilkan daftar status pegawai
     */
    public function index()
    {
        $statusPegawai = RefStatusPegawai::orderBy('status_pegawai', 'asc')->get();
        
        return response()->json(['status_pegawai' => $statusPegawai]);
    }

    /**
     * Menyimpan data status pegawai baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status_pegawai' => 'required|string|max:255|unique:ref_status_pegawai,status_pegawai',
        ]);

        $statusPegawai = RefStatusPegawai::create($validated);

        return response()->json([
            'message' => 'Data status pegawai berhasil disimpan',
            'status_pegawai' => $statusPegawai
        ], 201);
    }

    /**
     * Update data status pegawai
     */
    public function update(Request $request, $id)
    {
        $statusPegawai = RefStatusPegawai::findOrFail($id);
        
        $validated = $request->validate([
            'status_pegawai' => 'required|string|max:255|unique:ref_status_pegawai,status_pegawai,'.$statusPegawai->id,
        ]);

        $statusPegawai->update($validated);

        return response()->json([
            'message' => 'Data status pegawai berhasil diperbarui',
            'status_pegawai' => $statusPegawai
        ]);
    }

    /**
     * Menghapus data status pegawai
     */
    public function destroy($id)
    {
        $statusPegawai = RefStatusPegawai::findOrFail($id);
        $statusPegawai->delete();

        return response()->json([
            'message' => 'Data status pegawai berhasil dihapus'
        ]);
    }
} 