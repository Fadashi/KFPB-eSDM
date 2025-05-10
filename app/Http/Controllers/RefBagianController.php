<?php

namespace App\Http\Controllers;

use App\Models\RefBagian;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefBagianController extends Controller
{
    /**
     * Menampilkan daftar bagian
     */
    public function index()
    {
        $bagian = RefBagian::all();
        
        return response()->json(['bagian' => $bagian]);
    }

    /**
     * Menyimpan data bagian baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_bagian,kode',
            'bagian' => 'required|string|max:255',
        ]);

        $bagian = RefBagian::create($validated);

        return response()->json([
            'message' => 'Data bagian berhasil disimpan',
            'bagian' => $bagian
        ], 201);
    }

    /**
     * Update data bagian
     */
    public function update(Request $request, $id)
    {
        $bagian = RefBagian::findOrFail($id);
        
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_bagian,kode,'.$bagian->id,
            'bagian' => 'required|string|max:255',
        ]);

        $bagian->update($validated);

        return response()->json([
            'message' => 'Data bagian berhasil diperbarui',
            'bagian' => $bagian
        ]);
    }

    /**
     * Menghapus data bagian
     */
    public function destroy($id)
    {
        $bagian = RefBagian::findOrFail($id);
        $bagian->delete();

        return response()->json([
            'message' => 'Data bagian berhasil dihapus'
        ]);
    }
}
