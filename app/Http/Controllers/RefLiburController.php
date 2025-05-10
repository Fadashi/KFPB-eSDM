<?php

namespace App\Http\Controllers;

use App\Models\RefLibur;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefLiburController extends Controller
{
    /**
     * Menampilkan daftar hari libur
     */
    public function index()
    {
        $libur = RefLibur::orderBy('tanggal_libur', 'desc')->get();
        
        return response()->json(['libur' => $libur]);
    }

    /**
     * Menyimpan data libur baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_libur' => 'required|string|max:255',
            'tanggal_libur' => 'required|date',
        ]);

        $libur = RefLibur::create($validated);

        return response()->json([
            'message' => 'Data libur berhasil disimpan',
            'libur' => $libur
        ], 201);
    }

    /**
     * Update data libur
     */
    public function update(Request $request, $id)
    {
        $libur = RefLibur::findOrFail($id);
        
        $validated = $request->validate([
            'nama_libur' => 'required|string|max:255',
            'tanggal_libur' => 'required|date',
        ]);

        $libur->update($validated);

        return response()->json([
            'message' => 'Data libur berhasil diperbarui',
            'libur' => $libur
        ]);
    }

    /**
     * Menghapus data libur
     */
    public function destroy($id)
    {
        $libur = RefLibur::findOrFail($id);
        $libur->delete();

        return response()->json([
            'message' => 'Data libur berhasil dihapus'
        ]);
    }
} 