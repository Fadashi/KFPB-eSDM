<?php

namespace App\Http\Controllers;

use App\Models\RefJabatanFungsional;
use Illuminate\Http\Request;

class RefJabatanFungsionalController extends Controller
{
    /**
     * Menampilkan daftar jabatan fungsional
     */
    public function index()
    {
        $jabatanFungsional = RefJabatanFungsional::all();
        
        return response()->json(['jabatan_fungsional' => $jabatanFungsional]);
    }

    /**
     * Menyimpan data jabatan fungsional baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan_fungsional' => 'required|string|max:255|unique:ref_jabatan_fungsional,jabatan_fungsional',
            'kelas' => 'nullable|string|max:255',
            'umur_pensiun' => 'nullable|integer|min:0|max:100',
        ]);

        $jabatanFungsional = RefJabatanFungsional::create($validated);

        return response()->json([
            'message' => 'Data jabatan fungsional berhasil disimpan',
            'jabatan_fungsional' => $jabatanFungsional
        ], 201);
    }

    /**
     * Update data jabatan fungsional
     */
    public function update(Request $request, $id)
    {
        $jabatanFungsional = RefJabatanFungsional::findOrFail($id);
        
        $validated = $request->validate([
            'jabatan_fungsional' => 'required|string|max:255|unique:ref_jabatan_fungsional,jabatan_fungsional,'.$jabatanFungsional->id,
            'kelas' => 'nullable|string|max:255',
            'umur_pensiun' => 'nullable|integer|min:0|max:100',
        ]);

        $jabatanFungsional->update($validated);

        return response()->json([
            'message' => 'Data jabatan fungsional berhasil diperbarui',
            'jabatan_fungsional' => $jabatanFungsional
        ]);
    }

    /**
     * Menghapus data jabatan fungsional
     */
    public function destroy($id)
    {
        $jabatanFungsional = RefJabatanFungsional::findOrFail($id);
        $jabatanFungsional->delete();

        return response()->json([
            'message' => 'Data jabatan fungsional berhasil dihapus'
        ]);
    }
} 