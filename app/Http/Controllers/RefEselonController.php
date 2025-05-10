<?php

namespace App\Http\Controllers;

use App\Models\RefEselon;
use Illuminate\Http\Request;

class RefEselonController extends Controller
{
    /**
     * Menampilkan daftar eselon
     */
    public function index()
    {
        $eselon = RefEselon::all();
        
        return response()->json(['eselon' => $eselon]);
    }

    /**
     * Menyimpan data eselon baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'eselon' => 'required|string|max:255|unique:ref_eselon,eselon',
            'uraian' => 'nullable|string|max:255',
        ]);

        $eselon = RefEselon::create($validated);

        return response()->json([
            'message' => 'Data eselon berhasil disimpan',
            'eselon' => $eselon
        ], 201);
    }

    /**
     * Update data eselon
     */
    public function update(Request $request, $id)
    {
        $eselon = RefEselon::findOrFail($id);
        
        $validated = $request->validate([
            'eselon' => 'required|string|max:255|unique:ref_eselon,eselon,'.$eselon->id,
            'uraian' => 'nullable|string|max:255',
        ]);

        $eselon->update($validated);

        return response()->json([
            'message' => 'Data eselon berhasil diperbarui',
            'eselon' => $eselon
        ]);
    }

    /**
     * Menghapus data eselon
     */
    public function destroy($id)
    {
        $eselon = RefEselon::findOrFail($id);
        $eselon->delete();

        return response()->json([
            'message' => 'Data eselon berhasil dihapus'
        ]);
    }
} 