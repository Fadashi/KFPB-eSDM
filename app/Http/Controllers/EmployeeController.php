<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\City;
use App\Models\Department;
use App\Models\District;
use App\Models\Employee;
use App\Models\Province;
use App\Models\RefAgama;
use App\Models\RefBagian;
use App\Models\RefEselon;
use App\Models\RefJabatan;
use App\Models\RefJabatanFungsional;
use App\Models\RefJabatanStruktural;
use App\Models\RefStatusPegawai;
use App\Models\RefSubBagian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Menampilkan daftar pegawai
     */
    public function index()
    {
        // Ambil semua bagian
        $departments = RefBagian::all();
        
        // Ambil semua karyawan dengan relasi yang dibutuhkan
        $employees = Employee::with([
            'user',
            'department',
            'position',
            'province',
            'city',
            'district',
            'bank',
            'religion',
            'employeeType',
            'functionalPosition',
            'structuralPosition',
            'eselon',
        ])->get();
        
        // Kelompokkan karyawan berdasarkan bagian
        $employeesByDepartment = [];
        
        // Persiapkan array untuk semua bagian
        foreach ($departments as $department) {
            // Filter karyawan berdasarkan bagian
            $deptEmployees = $employees->filter(function($employee) use ($department) {
                return $employee->department_id == $department->id;
            })->values();
            
            // Tambahkan semua bagian ke array hasil, meskipun tidak memiliki karyawan
            $employeesByDepartment[] = [
                'id' => $department->id,
                'name' => $department->bagian,
                'employees' => $deptEmployees
            ];
        }
        
        // Tambahkan bagian "Belum Ditugaskan" untuk karyawan tanpa bagian
        $unassignedEmployees = $employees->filter(function($employee) {
            return $employee->department_id === null;
        })->values();
        
        // Tambahkan kategori "Belum Ditugaskan" jika ada karyawan tanpa bagian
        $employeesByDepartment[] = [
            'id' => 0,
            'name' => 'Belum Ditugaskan',
            'employees' => $unassignedEmployees
        ];

        return Inertia::render('Admin/Karyawan/Employees', [
            'departments' => $employeesByDepartment
        ]);
    }

    /**
     * Menampilkan form untuk menambah pegawai baru
     */
    public function create()
    {
        // Ambil data untuk dropdown
        $provinces = Province::all();
        $departments = RefBagian::all();
        $subDepartments = RefSubBagian::all();
        $banks = Bank::all();
        $religions = RefAgama::all();
        $employeeTypes = RefStatusPegawai::all();
        $functionalPositions = RefJabatanFungsional::all();
        $structuralPositions = RefJabatanStruktural::all();
        $eselons = RefEselon::all();
        $positions = RefJabatan::all();

        return Inertia::render('Admin/Karyawan/AddEmployees', [
            'provinces' => $provinces,
            'departments' => $departments,
            'subDepartments' => $subDepartments,
            'banks' => $banks,
            'religions' => $religions,
            'employeeTypes' => $employeeTypes,
            'functionalPositions' => $functionalPositions,
            'structuralPositions' => $structuralPositions,
            'eselons' => $eselons,
            'positions' => $positions
        ]);
    }

    /**
     * Mengambil daftar kota berdasarkan provinsi
     */
    public function getCities($provinceId)
    {
        try {
            $cities = City::where('province_id', $provinceId)
                ->select('id', 'name', 'type')
                ->orderBy('name')
                ->distinct()
                ->get();
            
            return response()->json(['cities' => $cities]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data kota: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil daftar kecamatan berdasarkan kota
     */
    public function getDistricts($cityId)
    {
        try {
            $districts = District::where('city_id', $cityId)
                ->select('id', 'name')
                ->orderBy('name')
                ->distinct()
                ->get();
            
            return response()->json(['districts' => $districts]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data kecamatan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan data pegawai baru
     */
    public function store(Request $request)
    {
        Log::info('Menerima request penyimpanan data karyawan baru', ['request' => $request->all()]);

        try {
            // Validasi input
            $validated = $request->validate([
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
                'nip' => 'required|string|max:50|unique:employees,nip',
                'entry_level' => 'nullable|string|max:100',
                'initial' => 'nullable|string|max:10',
                'name' => 'required|string|max:255',
                'gender' => 'required|in:L,P',
                'birth_place' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date',
                'age' => 'nullable|integer',
                'address' => 'required|string',
                'province_id' => 'nullable|exists:provinces,id',
                'city_id' => 'nullable|exists:cities,id',
                'district_id' => 'nullable|exists:districts,id',
                'temporary_address' => 'nullable|string',
                'email' => 'required|email|unique:employees,email|unique:users,email',
                'home_phone' => 'nullable|string|max:20',
                'mobile_phone' => 'nullable|string|max:20',
                'fax' => 'nullable|string|max:20',
                'ktp' => 'required|string|max:20|unique:employees,ktp',
                'npwp' => 'nullable|string|max:30|unique:employees,npwp',
                'department_id' => 'nullable|exists:ref_bagian,id',
                'bank_id' => 'nullable|exists:banks,id',
                'account_number' => 'nullable|string|max:50',
                'jamsostek' => 'nullable|string|max:50',
                'dplk' => 'nullable|string|max:50',
                'inhealth' => 'nullable|string|max:50',
                'religion_id' => 'nullable|exists:ref_agama,id',
                'employee_type_id' => 'nullable|exists:ref_status_pegawai,id',
                'grade' => 'nullable|string|max:50',
                'functional_position_id' => 'nullable|exists:ref_jabatan_fungsional,id',
                'structural_position_id' => 'nullable|exists:ref_jabatan_struktural,id',
                'sub_department_id' => 'nullable|exists:ref_subbagian,id',
                'eselon_id' => 'nullable|exists:ref_eselon,id',
                'marital_status' => 'nullable|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati',
                'employee_status' => 'required|in:Aktif,Non Aktif,Cuti',
                'join_date' => 'required|date',
                'contract_end_date' => 'nullable|date|after_or_equal:join_date',
                'education' => 'nullable|in:SD,SMP,SMA,D3,S1,S2,S3',
                'position_date' => 'nullable|date|after_or_equal:join_date',
                'position_id' => 'nullable|exists:ref_jabatan,id',
                'status' => 'required|in:Aktif,Non Aktif,Cuti',
                
                // Data Akun
                'username' => 'required|string|max:50|unique:users,name',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,atasan,pegawai',
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal: ', ['errors' => $e->errors()]);
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        Log::info('Data karyawan tervalidasi: ' . $request->name);

        // Upload foto jika ada
        $photoPath = null;
        if ($request->hasFile('photo')) {
            try {
                $photoPath = $request->file('photo')->store('employee-photos', 'public');
                Log::info('Foto karyawan berhasil diunggah: ' . $photoPath);
            } catch (\Exception $e) {
                Log::error('Error saat mengunggah foto: ' . $e->getMessage());
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Gagal mengunggah foto: ' . $e->getMessage()], 500);
                }
                return redirect()->back()->withErrors(['photo' => 'Gagal mengunggah foto: ' . $e->getMessage()])->withInput();
            }
        }

        try {
            // Mulai transaksi database
            DB::beginTransaction();
            Log::info('Mulai transaksi database untuk menyimpan karyawan baru');

            // Buat user baru
            $user = User::create([
                'name' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);
            Log::info('User baru dibuat dengan ID: ' . $user->id);

            // Buat data pegawai baru
            $employeeData = array_merge(
                collect($validated)->except(['photo', 'username', 'password', 'role'])->toArray(),
                ['photo' => $photoPath, 'user_id' => $user->id]
            );

            $employee = Employee::create($employeeData);
            Log::info('Karyawan baru berhasil dibuat dengan ID: ' . $employee->id);

            // Commit transaksi
            DB::commit();
            Log::info('Transaksi database berhasil untuk karyawan: ' . $employee->name);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data karyawan berhasil ditambahkan',
                    'employee' => $employee
                ]);
            }

            return redirect()->route('admin.employees')->with('success', 'Data karyawan berhasil ditambahkan');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            Log::error('Error saat menyimpan karyawan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Hapus foto jika sudah diupload
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
                Log::info('Foto dihapus karena transaksi gagal: ' . $photoPath);
            }

            $errorMessage = env('APP_DEBUG') 
                ? 'Terjadi kesalahan: ' . $e->getMessage()
                : 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';

            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMessage], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }

    /**
     * Menampilkan detail pegawai
     */
    public function show($id)
    {
        $employee = Employee::with([
            'user',
            'department',
            'province',
            'city',
            'district',
            'bank',
            'religion',
            'employeeType',
            'functionalPosition',
            'structuralPosition',
            'eselon',
        ])->findOrFail($id);

        return Inertia::render('Admin/Karyawan/DetailEmployee', [
            'employee' => $employee
        ]);
    }

    /**
     * Menampilkan form untuk edit pegawai
     */
    public function edit($id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        // Ambil data untuk dropdown
        $provinces = Province::all();
        $departments = RefBagian::all();
        $subDepartments = RefSubBagian::all();
        $banks = Bank::all();
        $religions = RefAgama::all();
        $employeeTypes = RefStatusPegawai::all();
        $functionalPositions = RefJabatanFungsional::all();
        $structuralPositions = RefJabatanStruktural::all();
        $eselons = RefEselon::all();

        // Ambil data kota dan kecamatan terkait jika ada
        $cities = collect();
        $districts = collect();

        if ($employee->province_id) {
            $cities = City::where('province_id', $employee->province_id)->get();
        }

        if ($employee->city_id) {
            $districts = District::where('city_id', $employee->city_id)->get();
        }

        return Inertia::render('Admin/Karyawan/EditEmployees', [
            'employee' => $employee,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'departments' => $departments,
            'subDepartments' => $subDepartments,
            'banks' => $banks,
            'religions' => $religions,
            'employeeTypes' => $employeeTypes,
            'functionalPositions' => $functionalPositions,
            'structuralPositions' => $structuralPositions,
            'eselons' => $eselons
        ]);
    }

    /**
     * Update data pegawai
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // Validasi input
        $validated = $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
            'nip' => ['required', 'string', 'max:50', Rule::unique('employees')->ignore($employee->id)],
            'entry_level' => 'nullable|string|max:100',
            'initial' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer',
            'address' => 'required|string',
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'temporary_address' => 'nullable|string',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'home_phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'ktp' => 'required|string|max:20',
            'npwp' => 'nullable|string|max:30',
            'department_id' => 'nullable|exists:ref_bagian,id',
            'bank_id' => 'nullable|exists:banks,id',
            'account_number' => 'nullable|string|max:50',
            'jamsostek' => 'nullable|string|max:50',
            'dplk' => 'nullable|string|max:50',
            'inhealth' => 'nullable|string|max:50',
            'religion_id' => 'nullable|exists:ref_agama,id',
            'employee_type_id' => 'nullable|exists:ref_status_pegawai,id',
            'grade' => 'nullable|string|max:50',
            'functional_position_id' => 'nullable|exists:ref_jabatan_fungsional,id',
            'structural_position_id' => 'nullable|exists:ref_jabatan_struktural,id',
            'sub_department_id' => 'nullable|exists:ref_subbagian,id',
            'eselon_id' => 'nullable|exists:ref_eselon,id',
            'marital_status' => 'nullable|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati',
            'employee_status' => 'required|in:Aktif,Non Aktif,Cuti',
            'join_date' => 'required|date',
            'contract_end_date' => 'nullable|date',
            'education' => 'nullable|in:SD,SMP,SMA,D3,S1,S2,S3',
            'position_date' => 'nullable|date',
            'position_id' => 'nullable|exists:ref_jabatan,id',
            'status' => 'required|in:Aktif,Non Aktif,Cuti',
            
            // Data Akun
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'name')->ignore($employee->user_id)],
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|in:admin,atasan,pegawai',
        ]);

        // Upload foto jika ada
        $photoPath = $employee->photo;
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            
            $photoPath = $request->file('photo')->store('employee-photos', 'public');
        }

        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Update data user jika ada perubahan
            if ($employee->user_id && ($request->filled('username') || $request->filled('password') || $request->filled('role'))) {
                $userData = [];
                
                if ($request->filled('username')) {
                    $userData['name'] = $validated['username'];
                }
                
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($validated['password']);
                }
                
                if ($request->filled('role')) {
                    $userData['role'] = $validated['role'];
                }
                
                if ($request->filled('email') && $employee->email != $validated['email']) {
                    $userData['email'] = $validated['email'];
                }
                
                if (!empty($userData)) {
                    User::where('id', $employee->user_id)->update($userData);
                }
            }

            // Update data pegawai
            $employeeData = array_merge(
                collect($validated)->except(['photo', 'username', 'password', 'role'])->toArray(),
                ['photo' => $photoPath]
            );

            $employee->update($employeeData);

            // Commit transaksi
            DB::commit();

            return redirect()->route('admin.employees')->with('success', 'Data pegawai berhasil diperbarui');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();

            // Hapus foto baru jika sudah diupload dan terjadi error
            if ($request->hasFile('photo') && $photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghapus data pegawai
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        
        try {
            // Mulai transaksi database
            DB::beginTransaction();
            
            // Hapus foto jika ada
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            
            // Simpan user_id untuk dihapus setelah menghapus pegawai
            $userId = $employee->user_id;
            
            // Hapus data pegawai
            $employee->delete();
            
            // Hapus data user jika ada
            if ($userId) {
                User::destroy($userId);
            }
            
            // Commit transaksi
            DB::commit();
            
            return redirect()->route('admin.employees')->with('success', 'Data pegawai berhasil dihapus');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
} 