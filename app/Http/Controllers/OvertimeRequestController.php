<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $overtimeRequests = OvertimeRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($overtimeRequests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'mulai' => 'required',
            'selesai' => 'required|after:mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:2048'
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('overtime-attachments', 'public');
        }

        $overtimeRequest = OvertimeRequest::create([
            'user_id' => Auth::id(),
            'tanggal' => $request->tanggal,
            'mulai' => $request->mulai,
            'selesai' => $request->selesai,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath,
            'status' => 'Menunggu'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lembur berhasil dikirim',
            'data' => $overtimeRequest
        ]);
    }

    public function show(OvertimeRequest $overtimeRequest)
    {
        if ($overtimeRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json($overtimeRequest);
    }

    public function update(Request $request, OvertimeRequest $overtimeRequest)
    {
        if ($overtimeRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($overtimeRequest->status !== 'Menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah pengajuan lembur yang sudah diproses'
            ], 400);
        }

        $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'mulai' => 'required',
            'selesai' => 'required|after:mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:2048'
        ]);

        if ($request->hasFile('lampiran')) {
            if ($overtimeRequest->lampiran) {
                Storage::disk('public')->delete($overtimeRequest->lampiran);
            }
            $lampiranPath = $request->file('lampiran')->store('overtime-attachments', 'public');
        } else {
            $lampiranPath = $overtimeRequest->lampiran;
        }

        $overtimeRequest->update([
            'tanggal' => $request->tanggal,
            'mulai' => $request->mulai,
            'selesai' => $request->selesai,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lembur berhasil diperbarui',
            'data' => $overtimeRequest
        ]);
    }

    public function destroy(OvertimeRequest $overtimeRequest)
    {
        if ($overtimeRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($overtimeRequest->status !== 'Menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus pengajuan lembur yang sudah diproses'
            ], 400);
        }

        if ($overtimeRequest->lampiran) {
            Storage::disk('public')->delete($overtimeRequest->lampiran);
        }

        $overtimeRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lembur berhasil dihapus'
        ]);
    }

    public function getPendingRequests()
    {
        $requests = OvertimeRequest::with('user')
            ->where('status', 'Menunggu')
            ->get();
        return response()->json($requests);
    }

    public function approve($id)
    {
        $request = OvertimeRequest::findOrFail($id);
        $request->status = 'Disetujui';
        $request->save();
        return response()->json(['message' => 'Pengajuan lembur berhasil disetujui']);
    }

    public function reject($id)
    {
        $request = OvertimeRequest::findOrFail($id);
        $request->status = 'Ditolak';
        $request->save();
        return response()->json(['message' => 'Pengajuan lembur berhasil ditolak']);
    }
} 