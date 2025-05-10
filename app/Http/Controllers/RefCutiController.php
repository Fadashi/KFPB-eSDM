<?php

namespace App\Http\Controllers;

use App\Models\RefCuti;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefCutiController extends Controller
{
    /**
     * Menampilkan daftar jenis cuti
     */
    public function index()
    {
        $cuti = RefCuti::orderBy('nama_cuti', 'asc')->get();
        
        return response()->json(['cuti' => $cuti]);
    }

    /**
     * Menyimpan data cuti baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_cuti' => 'required|string|max:255|unique:ref_cuti,nama_cuti',
            'jatah_cuti' => 'required|integer|min:0',
        ]);

        $cuti = RefCuti::create($validated);

        return response()->json([
            'message' => 'Data cuti berhasil disimpan',
            'cuti' => $cuti
        ], 201);
    }

    /**
     * Update data cuti
     */
    public function update(Request $request, $id)
    {
        $cuti = RefCuti::findOrFail($id);
        
        $validated = $request->validate([
            'nama_cuti' => 'required|string|max:255|unique:ref_cuti,nama_cuti,'.$cuti->id,
            'jatah_cuti' => 'required|integer|min:0',
        ]);

        $cuti->update($validated);

        return response()->json([
            'message' => 'Data cuti berhasil diperbarui',
            'cuti' => $cuti
        ]);
    }

    /**
     * Menghapus data cuti
     */
    public function destroy($id)
    {
        $cuti = RefCuti::findOrFail($id);
        $cuti->delete();

        return response()->json([
            'message' => 'Data cuti berhasil dihapus'
        ]);
    }
} 