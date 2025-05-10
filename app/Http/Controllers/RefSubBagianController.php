<?php

namespace App\Http\Controllers;

use App\Models\RefSubBagian;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefSubBagianController extends Controller
{
    /**
     * Menampilkan daftar sub bagian
     */
    public function index()
    {
        $subbagian = RefSubBagian::all();
        
        return response()->json(['subbagian' => $subbagian]);
    }

    /**
     * Menyimpan data sub bagian baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_subbagian,kode',
            'subbagian' => 'required|string|max:255',
        ]);

        $subbagian = RefSubBagian::create($validated);

        return response()->json([
            'message' => 'Data sub bagian berhasil disimpan',
            'subbagian' => $subbagian
        ], 201);
    }

    /**
     * Update data sub bagian
     */
    public function update(Request $request, $id)
    {
        $subbagian = RefSubBagian::findOrFail($id);
        
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_subbagian,kode,'.$subbagian->id,
            'subbagian' => 'required|string|max:255',
        ]);

        $subbagian->update($validated);

        return response()->json([
            'message' => 'Data sub bagian berhasil diperbarui',
            'subbagian' => $subbagian
        ]);
    }

    /**
     * Menghapus data sub bagian
     */
    public function destroy($id)
    {
        $subbagian = RefSubBagian::findOrFail($id);
        $subbagian->delete();

        return response()->json([
            'message' => 'Data sub bagian berhasil dihapus'
        ]);
    }
}
