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
            'type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        // Hitung jumlah hari cuti
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $endDate->diffInDays($startDate) + 1;

        // Upload attachment jika ada
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
            'days' => $days
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil dikirim',
            'data' => $leaveRequest
        ]);
    }

    public function show(LeaveRequest $leaveRequest)
    {
        // Pastikan user hanya bisa melihat pengajuan cuti miliknya
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
        // Pastikan user hanya bisa mengupdate pengajuan cuti miliknya
        if ($leaveRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Pastikan status masih pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah pengajuan cuti yang sudah diproses'
            ], 400);
        }

        $request->validate([
            'type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        // Hitung jumlah hari cuti
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $endDate->diffInDays($startDate) + 1;

        // Upload attachment baru jika ada
        if ($request->hasFile('attachment')) {
            // Hapus attachment lama jika ada
            if ($leaveRequest->attachment) {
                Storage::disk('public')->delete($leaveRequest->attachment);
            }
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        } else {
            $attachmentPath = $leaveRequest->attachment;
        }

        $leaveRequest->update([
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'days' => $days
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil diperbarui',
            'data' => $leaveRequest
        ]);
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        // Pastikan user hanya bisa menghapus pengajuan cuti miliknya
        if ($leaveRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Pastikan status masih pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus pengajuan cuti yang sudah diproses'
            ], 400);
        }

        // Hapus attachment jika ada
        if ($leaveRequest->attachment) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }

        $leaveRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil dihapus'
        ]);
    }
} 