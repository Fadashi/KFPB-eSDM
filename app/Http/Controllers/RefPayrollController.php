<?php

namespace App\Http\Controllers;

use App\Models\RefPayroll;
use Illuminate\Http\Request;

class RefPayrollController extends Controller
{
    /**
     * Menampilkan daftar payroll
     */
    public function index()
    {
        $payroll = RefPayroll::all();
        
        return response()->json(['payroll' => $payroll]);
    }

    /**
     * Menyimpan data payroll baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_payroll,kode',
            'payroll' => 'required|string|max:255',
            'penambah' => 'required|in:ya,tidak',
            'pengurang' => 'required|in:ya,tidak',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $payroll = RefPayroll::create($validated);

        return response()->json([
            'message' => 'Data payroll berhasil disimpan',
            'payroll' => $payroll
        ], 201);
    }

    /**
     * Update data payroll
     */
    public function update(Request $request, $id)
    {
        $payroll = RefPayroll::findOrFail($id);
        
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:ref_payroll,kode,'.$payroll->id,
            'payroll' => 'required|string|max:255',
            'penambah' => 'required|in:ya,tidak',
            'pengurang' => 'required|in:ya,tidak',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $payroll->update($validated);

        return response()->json([
            'message' => 'Data payroll berhasil diperbarui',
            'payroll' => $payroll
        ]);
    }

    /**
     * Menghapus data payroll
     */
    public function destroy($id)
    {
        $payroll = RefPayroll::findOrFail($id);
        $payroll->delete();

        return response()->json([
            'message' => 'Data payroll berhasil dihapus'
        ]);
    }
} 