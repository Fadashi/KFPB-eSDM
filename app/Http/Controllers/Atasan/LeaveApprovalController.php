<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        // Ambil semua pengajuan cuti yang pending
        $pendingRequests = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil riwayat pengajuan cuti yang sudah diproses
        $processedRequests = LeaveRequest::with('user')
            ->where('status', '!=', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'pending' => $pendingRequests,
            'processed' => $processedRequests
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        // Validasi request
        $request->validate([
            'rejection_reason' => 'nullable|string|max:255'
        ]);

        // Update status pengajuan cuti
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
            'rejection_reason' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil disetujui',
            'data' => $leaveRequest
        ]);
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        // Validasi request
        $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);

        // Update status pengajuan cuti
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil ditolak',
            'data' => $leaveRequest
        ]);
    }
} 