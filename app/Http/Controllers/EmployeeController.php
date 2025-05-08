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
        
        // Ambil semua karyawan dengan relasi yang dibutuhkan - ubah urutan eager loading untuk subDepartment
        $employees = Employee::with([
            'subDepartment', // Pindahkan ke paling atas agar diprioritaskan
            'department',
            'employeeType',
            'functionalPosition',
            'structuralPosition',
            'position',
            'user',
            'province',
            'city',
            'district',
            'bank',
            'religion',
            'eselon',
        ])->get();
        
        // Debug: memastikan relasi sub bagian terhubung dengan benar
        foreach ($employees as $employee) {
            if ($employee->sub_department_id) {
                // Gunakan firstOrFind untuk memastikan data ditemukan
                $subDept = RefSubBagian::where('id', $employee->sub_department_id)->first();
                if ($subDept) {
                    // Manual attach jika relasi tidak terbaca dengan benar
                    $employee->debug_subbagian = $subDept->subbagian;
                    // Pastikan subDepartment terbaca dengan benar
                    if (!$employee->subDepartment) {
                        $employee->setAttribute('subDepartment', $subDept);
                    }
                }
            }
        }
        
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
     * Mengambil daftar karyawan untuk dropdown
     */
    public function getEmployees()
    {
        try {
            $employees = Employee::select('id', 'name', 'nip')
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get();
            
            return response()->json(['employees' => $employees]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data karyawan: ' . $e->getMessage()], 500);
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
                // Hapus foto lama jika ada
                if ($photoPath && Storage::disk('public')->exists('employee-photos/' . $photoPath)) {
                    Storage::disk('public')->delete('employee-photos/' . $photoPath);
                }
                
                $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->storeAs('public/employee-photos', $photoName);
                $photoPath = $photoName;
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
     * Menampilkan form untuk mengedit data pegawai
     */
    public function edit($id)
    {
        // Cari karyawan berdasarkan ID
        $employee = Employee::with([
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
        ])->findOrFail($id);
        
        // Tambahkan URL foto ke objek karyawan
        $employee->photo_url = $employee->photo_url;
        
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

        return Inertia::render('Admin/Karyawan/EditEmployees', [
            'employee' => $employee,
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
     * Update data pegawai
     */
    public function update(Request $request, $id)
    {
        Log::info('Menerima request update data karyawan', ['request' => $request->all()]);

        try {
            // Cari karyawan yang akan diupdate
            $employee = Employee::findOrFail($id);
            
            // Validasi input
            $validated = $request->validate([
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
                'nip' => ['required', 'string', 'max:50', Rule::unique('employees', 'nip')->ignore($id)],
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
                'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($id), Rule::unique('users', 'email')->ignore($employee->user_id)],
                'home_phone' => 'nullable|string|max:20',
                'mobile_phone' => 'nullable|string|max:20',
                'fax' => 'nullable|string|max:20',
                'ktp' => ['required', 'string', 'max:20', Rule::unique('employees', 'ktp')->ignore($id)],
                'npwp' => ['nullable', 'string', 'max:30', Rule::unique('employees', 'npwp')->ignore($id)],
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
                'join_date' => 'nullable|date',
                'contract_end_date' => 'nullable|date',
                'education' => 'nullable|string|max:255',
                'position_date' => 'nullable|date',
                'position_id' => 'nullable|exists:ref_jabatan,id',
                'status' => 'required|in:Aktif,Non Aktif',
            ]);

            // Mulai transaksi database
            DB::beginTransaction();

            // Upload foto jika ada
            if ($request->hasFile('photo')) {
                // Hapus foto lama jika ada
                if ($employee->photo && Storage::disk('public')->exists('employee-photos/' . $employee->photo)) {
                    Storage::disk('public')->delete('employee-photos/' . $employee->photo);
                }
                
                // Upload foto baru
                $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
                $request->file('photo')->storeAs('public/employee-photos', $photoName);
                $employee->photo = $photoName;
            }

            // Update data karyawan
            $employee->nip = $validated['nip'];
            $employee->entry_level = $validated['entry_level'];
            $employee->initial = $validated['initial'];
            $employee->name = $validated['name'];
            $employee->gender = $validated['gender'];
            $employee->birth_place = $validated['birth_place'];
            $employee->birth_date = $validated['birth_date'];
            $employee->age = $validated['age'];
            $employee->address = $validated['address'];
            $employee->province_id = $validated['province_id'];
            $employee->city_id = $validated['city_id'];
            $employee->district_id = $validated['district_id'];
            $employee->temporary_address = $validated['temporary_address'];
            $employee->email = $validated['email'];
            $employee->home_phone = $validated['home_phone'];
            $employee->mobile_phone = $validated['mobile_phone'];
            $employee->fax = $validated['fax'];
            $employee->ktp = $validated['ktp'];
            $employee->npwp = $validated['npwp'];
            $employee->department_id = $validated['department_id'];
            $employee->bank_id = $validated['bank_id'];
            $employee->account_number = $validated['account_number'];
            $employee->jamsostek = $validated['jamsostek'];
            $employee->dplk = $validated['dplk'];
            $employee->inhealth = $validated['inhealth'];
            $employee->religion_id = $validated['religion_id'];
            $employee->employee_type_id = $validated['employee_type_id'];
            $employee->grade = $validated['grade'];
            $employee->functional_position_id = $validated['functional_position_id'];
            $employee->structural_position_id = $validated['structural_position_id'];
            $employee->sub_department_id = $validated['sub_department_id'];
            $employee->eselon_id = $validated['eselon_id'];
            $employee->marital_status = $validated['marital_status'];
            $employee->employee_status = $validated['employee_status'];
            $employee->join_date = $validated['join_date'];
            $employee->contract_end_date = $validated['contract_end_date'];
            $employee->education = $validated['education'];
            $employee->position_date = $validated['position_date'];
            $employee->position_id = $validated['position_id'];
            $employee->status = $validated['status'];
            
            $employee->save();

            // Update user terkait jika ada
            if ($employee->user_id) {
                $user = User::find($employee->user_id);
                if ($user) {
                    $user->name = $validated['name'];
                    $user->email = $validated['email'];
                    $user->save();
                }
            }

            DB::commit();
            
            return redirect()->route('admin.employees')->with('success', 'Data karyawan berhasil diperbarui.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat update karyawan: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()]);
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

    /**
     * Debugging dan refresh data sub bagian
     */
    public function refreshSubDepartments()
    {
        try {
            // Ambil semua karyawan
            $employees = Employee::all();
            $updatedCount = 0;
            $problems = [];

            foreach ($employees as $employee) {
                if ($employee->sub_department_id) {
                    // Cari data sub bagian
                    $subDept = RefSubBagian::find($employee->sub_department_id);
                    
                    if ($subDept) {
                        // Tambahkan log debug
                        $data = [
                            'employee_id' => $employee->id,
                            'employee_name' => $employee->name,
                            'sub_department_id' => $employee->sub_department_id,
                            'sub_department_name' => $subDept->subbagian
                        ];
                        
                        $updatedCount++;
                    } else {
                        // Catat karyawan dengan ID sub bagian tidak valid
                        $problems[] = [
                            'employee_id' => $employee->id,
                            'employee_name' => $employee->name,
                            'sub_department_id' => $employee->sub_department_id,
                            'error' => 'Sub Bagian tidak ditemukan'
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Refresh selesai. $updatedCount data diperbarui.",
                'problems' => $problems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
} 