<?php

namespace App\Http\Controllers;

use App\Models\RefBerkas;
use Illuminate\Http\Request;

class RefBerkasController extends Controller
{
    /**
     * Menampilkan daftar berkas
     */
    public function index()
    {
        $berkas = RefBerkas::all();
        
        return response()->json(['berkas' => $berkas]);
    }

    /**
     * Menyimpan data berkas baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_berkas' => 'required|string|max:255|unique:ref_berkas,nama_berkas',
        ]);

        $berkas = RefBerkas::create($validated);

        return response()->json([
            'message' => 'Data berkas berhasil disimpan',
            'berkas' => $berkas
        ], 201);
    }

    /**
     * Update data berkas
     */
    public function update(Request $request, $id)
    {
        $berkas = RefBerkas::findOrFail($id);
        
        $validated = $request->validate([
            'nama_berkas' => 'required|string|max:255|unique:ref_berkas,nama_berkas,'.$berkas->id,
        ]);

        $berkas->update($validated);

        return response()->json([
            'message' => 'Data berkas berhasil diperbarui',
            'berkas' => $berkas
        ]);
    }

    /**
     * Menghapus data berkas
     */
    public function destroy($id)
    {
        $berkas = RefBerkas::findOrFail($id);
        $berkas->delete();

        return response()->json([
            'message' => 'Data berkas berhasil dihapus'
        ]);
    }
} 