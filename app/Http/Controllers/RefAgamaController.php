<?php

namespace App\Http\Controllers;

use App\Models\RefAgama;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefAgamaController extends Controller
{
    /**
     * Menampilkan daftar agama
     */
    public function index()
    {
        $agama = RefAgama::all();
        
        return response()->json(['agama' => $agama]);
    }

    /**
     * Menyimpan data agama baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'agama' => 'required|string|max:255|unique:ref_agama,agama',
        ]);

        $agama = RefAgama::create($validated);

        return response()->json([
            'message' => 'Data agama berhasil disimpan',
            'agama' => $agama
        ], 201);
    }

    /**
     * Update data agama
     */
    public function update(Request $request, $id)
    {
        $agama = RefAgama::findOrFail($id);
        
        $validated = $request->validate([
            'agama' => 'required|string|max:255|unique:ref_agama,agama,'.$agama->id,
        ]);

        $agama->update($validated);

        return response()->json([
            'message' => 'Data agama berhasil diperbarui',
            'agama' => $agama
        ]);
    }

    /**
     * Menghapus data agama
     */
    public function destroy($id)
    {
        $agama = RefAgama::findOrFail($id);
        $agama->delete();

        return response()->json([
            'message' => 'Data agama berhasil dihapus'
        ]);
    }
} 