<?php

namespace App\Http\Controllers;

use App\Models\RefJabatanStruktural;
use Illuminate\Http\Request;

class RefJabatanStrukturalController extends Controller
{
    /**
     * Menampilkan daftar jabatan struktural
     */
    public function index()
    {
        $jabatanStruktural = RefJabatanStruktural::all();
        
        return response()->json(['jabatan_struktural' => $jabatanStruktural]);
    }

    /**
     * Menyimpan data jabatan struktural baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan_struktural' => 'required|string|max:255|unique:ref_jabatan_struktural,jabatan_struktural',
            'kelas' => 'required|string|max:255',
        ]);

        $jabatanStruktural = RefJabatanStruktural::create($validated);

        return response()->json([
            'message' => 'Data jabatan struktural berhasil disimpan',
            'jabatan_struktural' => $jabatanStruktural
        ], 201);
    }

    /**
     * Update data jabatan struktural
     */
    public function update(Request $request, $id)
    {
        $jabatanStruktural = RefJabatanStruktural::findOrFail($id);
        
        $validated = $request->validate([
            'jabatan_struktural' => 'required|string|max:255|unique:ref_jabatan_struktural,jabatan_struktural,'.$jabatanStruktural->id,
            'kelas' => 'required|string|max:255',
        ]);

        $jabatanStruktural->update($validated);

        return response()->json([
            'message' => 'Data jabatan struktural berhasil diperbarui',
            'jabatan_struktural' => $jabatanStruktural
        ]);
    }

    /**
     * Menghapus data jabatan struktural
     */
    public function destroy($id)
    {
        $jabatanStruktural = RefJabatanStruktural::findOrFail($id);
        $jabatanStruktural->delete();

        return response()->json([
            'message' => 'Data jabatan struktural berhasil dihapus'
        ]);
    }
} 