<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($leaveRequests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|string',
            'mulai' => 'required|date|after_or_equal:today',
            'selesai' => 'required|date|after_or_equal:mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:2048'
        ]);

        // Upload lampiran jika ada
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('leave-attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'jenis' => $request->jenis,
            'mulai' => $request->mulai,
            'selesai' => $request->selesai,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath,
            'status' => 'Menunggu'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil dikirim',
            'data' => $leaveRequest
        ]);
    }

    public function show(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json($leaveRequest);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($leaveRequest->status !== 'Menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah pengajuan cuti yang sudah diproses'
            ], 400);
        }

        $request->validate([
            'jenis' => 'required|string',
            'mulai' => 'required|date|after_or_equal:today',
            'selesai' => 'required|date|after_or_equal:mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:2048'
        ]);

        if ($request->hasFile('lampiran')) {
            if ($leaveRequest->lampiran) {
                Storage::disk('public')->delete($leaveRequest->lampiran);
            }
            $lampiranPath = $request->file('lampiran')->store('leave-attachments', 'public');
        } else {
            $lampiranPath = $leaveRequest->lampiran;
        }

        $leaveRequest->update([
            'jenis' => $request->jenis,
            'mulai' => $request->mulai,
            'selesai' => $request->selesai,
            'alasan' => $request->alasan,
            'lampiran' => $lampiranPath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil diperbarui',
            'data' => $leaveRequest
        ]);
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($leaveRequest->status !== 'Menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus pengajuan cuti yang sudah diproses'
            ], 400);
        }

        if ($leaveRequest->lampiran) {
            Storage::disk('public')->delete($leaveRequest->lampiran);
        }

        $leaveRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil dihapus'
        ]);
    }
} 