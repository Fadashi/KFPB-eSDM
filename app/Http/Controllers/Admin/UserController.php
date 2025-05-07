<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\AuditHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $role = $request->input('role', 'all');
        
        $query = User::query()
            ->select('id', 'name', 'email', 'role', 'status');
        
        // Pencarian berdasarkan nama atau email
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan role
        if ($role !== 'all') {
            $query->where('role', $role);
        }
        
        $users = $query->paginate($perPage);
        
        // Transform data untuk menyesuaikan dengan tampilan
        $transformedUsers = $users->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->email, // Menggunakan email sebagai username
                'role' => $user->role,
                'status' => $user->status,
            ];
        });
        
        return Inertia::render('Admin/Users', [
            'users' => $transformedUsers,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'per_page' => $perPage,
            ],
        ]);
    }
    
    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:pegawai,atasan,admin',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['username'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'status' => 'Active',
        ]);
        
        // Catat ke audit trail
        AuditHelper::log(
            'tambah',
            'User',
            'User baru: ' . $user->name,
            null,
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status
            ]
        );
        
        return redirect()->route('admin.users')->with('success', 'User berhasil dibuat');
    }
    
    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Simpan data lama untuk audit trail
        $oldData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            // Tidak menyimpan password dalam audit trail
        ];
        
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|string|in:pegawai,atasan,admin',
            'status' => 'required|string|in:Active,Inactive',
        ];
        
        // Variabel untuk menandai perubahan password
        $passwordChanged = false;
        
        // Hanya validasi password jika diisi
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8';
            $passwordChanged = true;
        }
        
        $validated = $request->validate($rules);
        
        // Update user data
        $user->name = $validated['name'];
        $user->email = $validated['username'];
        $user->role = $validated['role'];
        $user->status = $validated['status'];
        
        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($validated['password']);
        }
        
        $user->save();
        
        // Data baru untuk audit trail
        $newData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            // Tidak menyimpan password dalam audit trail
        ];
        
        // Tambahkan informasi bahwa password telah diubah jika perlu
        if ($passwordChanged) {
            $newData['password'] = '[Password diubah]';
        }
        
        // Catat ke audit trail
        AuditHelper::log(
            'ubah',
            'User',
            'User: ' . $user->name . ($passwordChanged ? ' (Password diubah)' : ''),
            $oldData,
            $newData
        );
        
        return redirect()->route('admin.users')->with('success', 'User berhasil diupdate' . ($passwordChanged ? ' dan password diubah' : ''));
    }
    
    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Simpan data untuk audit trail
        $deletedData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status
        ];
        
        // Hapus user
        $user->delete();
        
        // Catat ke audit trail
        AuditHelper::log(
            'hapus',
            'User',
            'User: ' . $deletedData['name'],
            $deletedData,
            null
        );
        
        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus');
    }
} 