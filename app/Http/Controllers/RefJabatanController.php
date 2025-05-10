<?php

namespace App\Http\Controllers;

use App\Models\RefJabatan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefJabatanController extends Controller
{
    /**
     * Menampilkan daftar jabatan
     */
    public function index()
    {
        $jabatan = RefJabatan::all();
        
        return response()->json(['jabatan' => $jabatan]);
    }

    /**
     * Menyimpan data jabatan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_jabatan,kode',
            'jabatan' => 'required|string|max:255',
            'gaji_pokok' => 'required|numeric|min:0',
            'tunjangan' => 'required|numeric|min:0',
            'masa' => 'required|numeric|min:0',
        ]);

        $jabatan = RefJabatan::create($validated);

        return response()->json([
            'message' => 'Data jabatan berhasil disimpan',
            'jabatan' => $jabatan
        ], 201);
    }

    /**
     * Update data jabatan
     */
    public function update(Request $request, $id)
    {
        $jabatan = RefJabatan::findOrFail($id);
        
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_jabatan,kode,'.$jabatan->id,
            'jabatan' => 'required|string|max:255',
            'gaji_pokok' => 'required|numeric|min:0',
            'tunjangan' => 'required|numeric|min:0',
            'masa' => 'required|numeric|min:0',
        ]);

        $jabatan->update($validated);

        return response()->json([
            'message' => 'Data jabatan berhasil diperbarui',
            'jabatan' => $jabatan
        ]);
    }

    /**
     * Menghapus data jabatan
     */
    public function destroy($id)
    {
        $jabatan = RefJabatan::findOrFail($id);
        $jabatan->delete();

        return response()->json([
            'message' => 'Data jabatan berhasil dihapus'
        ]);
    }
}
