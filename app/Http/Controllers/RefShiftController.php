<?php

namespace App\Http\Controllers;

use App\Models\RefShift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefShiftController extends Controller
{
    /**
     * Menampilkan daftar shift
     */
    public function index()
    {
        $shift = RefShift::all();
        
        return response()->json(['shift' => $shift]);
    }

    /**
     * Menyimpan data shift baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift' => 'required|string|max:255',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $shift = RefShift::create($validated);

        return response()->json([
            'message' => 'Data shift berhasil disimpan',
            'shift' => $shift
        ], 201);
    }

    /**
     * Update data shift
     */
    public function update(Request $request, $id)
    {
        $shift = RefShift::findOrFail($id);
        
        $validated = $request->validate([
            'shift' => 'required|string|max:255',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $shift->update($validated);

        return response()->json([
            'message' => 'Data shift berhasil diperbarui',
            'shift' => $shift
        ]);
    }

    /**
     * Menghapus data shift
     */
    public function destroy($id)
    {
        $shift = RefShift::findOrFail($id);
        $shift->delete();

        return response()->json([
            'message' => 'Data shift berhasil dihapus'
        ]);
    }
}
