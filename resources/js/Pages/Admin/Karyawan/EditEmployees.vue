<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Menerima data dari controller
const props = defineProps({
    employee: Object,
    provinces: Array,
    departments: Array,
    subDepartments: Array,
    banks: Array,
    religions: Array,
    employeeTypes: Array,
    functionalPositions: Array,
    structuralPositions: Array,
    positions: Array,
    eselons: Array,
    errors: Object
});

const cities = ref([]);
const districts = ref([]);

// Inisialisasi form dengan data karyawan yang sudah ada
const form = useForm({
    photo: null,
    photoPreview: props.employee?.photo_url || null,
    nip: props.employee?.nip || '',
    entry_level: props.employee?.entry_level || '',
    initial: props.employee?.initial || '',
    name: props.employee?.name || '',
    gender: props.employee?.gender || '',
    birth_place: props.employee?.birth_place || '',
    birth_date: props.employee?.birth_date || '',
    age: props.employee?.age || '',
    address: props.employee?.address || '',
    province_id: props.employee?.province_id || '',
    city_id: props.employee?.city_id || '',
    district_id: props.employee?.district_id || '',
    temporary_address: props.employee?.temporary_address || '',
    email: props.employee?.email || '',
    home_phone: props.employee?.home_phone || '',
    mobile_phone: props.employee?.mobile_phone || '',
    fax: props.employee?.fax || '',
    ktp: props.employee?.ktp || '',
    npwp: props.employee?.npwp || '',
    department_id: props.employee?.department_id || '',
    bank_id: props.employee?.bank_id || '',
    account_number: props.employee?.account_number || '',
    jamsostek: props.employee?.jamsostek || '',
    dplk: props.employee?.dplk || '',
    inhealth: props.employee?.inhealth || '',
    religion_id: props.employee?.religion_id || '',
    employee_type_id: props.employee?.employee_type_id || '',
    grade: props.employee?.grade || '',
    functional_position_id: props.employee?.functional_position_id || '',
    structural_position_id: props.employee?.structural_position_id || '',
    sub_department_id: props.employee?.sub_department_id || '',
    eselon_id: props.employee?.eselon_id || '',
    marital_status: props.employee?.marital_status || '',
    employee_status: props.employee?.employee_status || 'Aktif',
    join_date: props.employee?.join_date || '',
    contract_end_date: props.employee?.contract_end_date || '',
    education: props.employee?.education || '',
    position_date: props.employee?.position_date || '',
    position_id: props.employee?.position_id || '',
    status: props.employee?.status || 'Aktif',
    _method: 'PUT', // Untuk Laravel PUT method
});

// Log ke konsol untuk debugging
console.log('Photo URL dari server:', props.employee?.photo_url);

// Fungsi untuk mengambil data kota dan kecamatan saat halaman dimuat
onMounted(async () => {
    if (form.province_id) {
        // Ambil data kota
        try {
            const response = await fetch(`/api/provinces/${form.province_id}/cities`);
            if (response.ok) {
                const data = await response.json();
                cities.value = data.cities || [];
            }
        } catch (error) {
            console.error('Error fetching cities:', error);
        }
    }
    
    if (form.city_id) {
        // Ambil data kecamatan
        try {
            const response = await fetch(`/api/cities/${form.city_id}/districts`);
            if (response.ok) {
                const data = await response.json();
                districts.value = data.districts || [];
            }
        } catch (error) {
            console.error('Error fetching districts:', error);
        }
    }
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    form.photo = file;

    // Validasi ukuran file (maksimal 2MB)
    if (file && file.size > 2 * 1024 * 1024) {
        alert('Ukuran file tidak boleh lebih dari 2MB');
        event.target.value = '';
        form.photo = null;
        form.photoPreview = props.employee?.photo_url || null; // Reset ke foto yang ada
        return;
    }

    // Create preview URL
    if (file) {
        form.photoPreview = URL.createObjectURL(file);
    } else {
        form.photoPreview = props.employee?.photo_url || null;
    }
};

// Mengambil data kota ketika provinsi berubah
watch(() => form.province_id, async (newValue) => {
    if (newValue) {
        form.city_id = '';
        form.district_id = '';
        districts.value = [];
        cities.value = []; // Reset cities array
        
        // Ambil data kota dari API
        try {
            const response = await fetch(`/api/provinces/${newValue}/cities`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.cities && Array.isArray(data.cities)) {
                cities.value = [...data.cities];
            } else {
                cities.value = [];
            }
        } catch (error) {
            console.error('Error fetching cities:', error);
            cities.value = [];
        }
    } else {
        cities.value = [];
        districts.value = [];
    }
});

// Mengambil data kecamatan ketika kota berubah
watch(() => form.city_id, async (newValue) => {
    if (newValue) {
        form.district_id = '';
        districts.value = [];
        
        // Ambil data kecamatan dari API
        try {
            const response = await fetch(`/api/cities/${newValue}/districts`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.districts && Array.isArray(data.districts)) {
                districts.value = [...data.districts];
            } else {
                districts.value = [];
            }
        } catch (error) {
            console.error('Error fetching districts:', error);
            districts.value = [];
        }
    } else {
        districts.value = [];
    }
});

// Hitung usia otomatis saat tanggal lahir berubah
watch(() => form.birth_date, (newValue) => {
    if (newValue) {
        const birthDate = new Date(newValue);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        form.age = age.toString();
    } else {
        form.age = '';
    }
});

const submitForm = () => {
    form.post(route('admin.employees.update', { id: props.employee.id }), {
        onSuccess: () => {
            // Redirect ke halaman list karyawan setelah berhasil update
            router.visit(route('admin.employees'));
        },
        onError: (errors) => {
            console.error('Error updating employee:', errors);
        }
    });
};

const cancelEdit = () => {
    router.visit(route('admin.employees'));
};
</script>

<template>
  <Head title="Edit Karyawan" />

  <AuthenticatedLayout>
    <template #header>
      <!-- Breadcrumbs -->
      <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex">
          <li class="flex items-center">
            <i class="fas fa-home text-blue-600 mr-1"></i>
            <a href="/admin/dashboard" class="text-blue-600 hover:text-blue-800 font-semibold">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
          </li>
          <li class="flex items-center">
            <a href="/admin/employees" class="text-blue-600 hover:text-blue-800 font-semibold">Karyawan</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
          </li>
          <li class="text-gray-700 font-semibold">Edit Karyawan</li>
        </ol>
      </nav>

      <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-900">Edit Data Karyawan</h1>
      </div>
    </template>

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Data Diri -->
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Diri</h2>
        <div class="flex flex-col items-center">
          <label class="block text-sm font-medium text-gray-700 mb-4">Foto</label>
          <div class="relative">
            <div class="profile-picture">
              <img
                v-if="form.photoPreview"
                :src="form.photoPreview"
                alt="Preview Foto"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-gray-400">
                <i class="fas fa-user text-7xl"></i>
              </div>
            </div>
            <div class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md border border-gray-200">
              <label class="cursor-pointer">
                <i class="fas fa-camera text-blue-600 text-lg"></i>
                <input
                  type="file"
                  @change="handleFileChange"
                  accept="image/*"
                  class="hidden"
                />
              </label>
            </div>
          </div>
        </div>

        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
          <input
            type="text"
            id="name"
            v-model="form.name"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required
          />
        </div>

        <div>
          <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
          <input
            type="text"
            id="nip"
            v-model="form.nip"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
          <div class="mt-2 space-x-4">
            <label class="inline-flex items-center">
              <input
                type="radio"
                v-model="form.gender"
                value="L"
                class="form-radio text-blue-600"
              />
              <span class="ml-2">Laki-Laki</span>
            </label>
            <label class="inline-flex items-center">
              <input
                type="radio"
                v-model="form.gender"
                value="P"
                class="form-radio text-blue-600"
              />
              <span class="ml-2">Perempuan</span>
            </label>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="birth_place" class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
            <input
              type="text"
              id="birth_place"
              v-model="form.birth_place"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
            <input
              type="date"
              id="birth_date"
              v-model="form.birth_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>

        <div>
          <label for="religion" class="block text-sm font-medium text-gray-700">Agama</label>
          <select
            id="religion_id"
            v-model="form.religion_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">-- Pilih Agama --</option>
            <option v-for="religion in props.religions" :key="religion.id" :value="religion.id">
              {{ religion.agama }}
            </option>
          </select>
        </div>

        <div>
          <label for="marital_status" class="block text-sm font-medium text-gray-700">Status Kawin</label>
          <select
            id="marital_status"
            v-model="form.marital_status"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required
          >
            <option value="">-- Status Kawin --</option>
            <option value="Belum Kawin">Belum Kawin</option>
            <option value="Kawin">Kawin</option>
            <option value="Cerai Hidup">Cerai Hidup</option>
            <option value="Cerai Mati">Cerai Mati</option>
          </select>
        </div>

        <div class="space-y-4">
          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
            <textarea
              id="address"
              v-model="form.address"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Mohon Isi Alamat dengan lengkap..."
              required
            ></textarea>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label for="province_id" class="block text-sm font-medium text-gray-700">Provinsi</label>
              <select
                id="province_id"
                v-model="form.province_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="">--Pilih Provinsi--</option>
                <option v-for="province in props.provinces" :key="province.id" :value="province.id">
                  {{ province.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="city_id" class="block text-sm font-medium text-gray-700">Kabupaten/Kota</label>
              <select
                id="city_id"
                v-model="form.city_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :disabled="!form.province_id || cities.length === 0"
              >
                <option value="">--Pilih Kabupaten/Kota--</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }} ({{ city.type }})
                </option>
              </select>
              <div v-if="form.province_id && cities.length === 0" class="text-xs text-gray-500 mt-1">Loading kota...</div>
              <div v-if="form.province_id && cities.length > 0" class="text-xs text-green-500 mt-1">{{ cities.length }} kota tersedia</div>
            </div>
            <div>
              <label for="district_id" class="block text-sm font-medium text-gray-700">Kecamatan</label>
              <select
                id="district_id"
                v-model="form.district_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :disabled="!form.city_id || districts.length === 0"
              >
                <option value="">--Pilih Kecamatan--</option>
                <option v-for="district in districts" :key="district.id" :value="district.id">
                  {{ district.name }}
                </option>
              </select>
              <div v-if="form.city_id && districts.length === 0" class="text-xs text-gray-500 mt-1">Loading kecamatan...</div>
              <div v-if="form.city_id && districts.length > 0" class="text-xs text-green-500 mt-1">{{ districts.length }} kecamatan tersedia</div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              id="email"
              v-model="form.email"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            />
          </div>
          <div>
            <label for="home_phone" class="block text-sm font-medium text-gray-700">Tlp. Rumah</label>
            <input
              type="tel"
              id="home_phone"
              v-model="form.home_phone"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="(021) 999-9999"
            />
          </div>
          <div>
            <label for="mobile_phone" class="block text-sm font-medium text-gray-700">No. HP</label>
            <input
              type="tel"
              id="mobile_phone"
              v-model="form.mobile_phone"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Isikan No Hp"
            />
          </div>
          <div>
            <label for="fax" class="block text-sm font-medium text-gray-700">No. Fax</label>
            <input
              type="tel"
              id="fax"
              v-model="form.fax"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Isikan No Fax"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="ktp" class="block text-sm font-medium text-gray-700">KTP</label>
            <input
              type="text"
              id="ktp"
              v-model="form.ktp"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            />
          </div>
          <div>
            <label for="npwp" class="block text-sm font-medium text-gray-700">NPWP</label>
            <input
              type="text"
              id="npwp"
              v-model="form.npwp"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="bank_id" class="block text-sm font-medium text-gray-700">Bank Transfer</label>
            <select
              id="bank_id"
              v-model="form.bank_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">-- Pilih Bank Transfer --</option>
              <option v-for="bank in props.banks" :key="bank.id" :value="bank.id">
                {{ bank.name }}
              </option>
            </select>
          </div>
          <div>
            <label for="account_number" class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
            <input
              type="text"
              id="account_number"
              v-model="form.account_number"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Data Kepegawaian -->
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Kepegawaian</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700">Bagian</label>
            <select
              id="department_id"
              v-model="form.department_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">-- Bagian --</option>
              <option v-for="department in props.departments" :key="department.id" :value="department.id">
                {{ department.bagian }}
              </option>
            </select>
          </div>
          <div>
            <label for="sub_department_id" class="block text-sm font-medium text-gray-700">Sub/Bagian</label>
            <select
              id="sub_department_id"
              v-model="form.sub_department_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">-- Pilih Sub Bagian --</option>
              <option v-for="subDepartment in props.subDepartments" :key="subDepartment.id" :value="subDepartment.id">
                {{ subDepartment.subbagian }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <label for="employee_type" class="block text-sm font-medium text-gray-700">Jenis Pegawai</label>
          <select
            id="employee_type_id"
            v-model="form.employee_type_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">-- Pilih Jenis Pegawai --</option>
            <option v-for="type in props.employeeTypes" :key="type.id" :value="type.id">
              {{ type.status_pegawai }}
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="structural_position" class="block text-sm font-medium text-gray-700">Jabatan Struktural</label>
            <select
              id="structural_position_id"
              v-model="form.structural_position_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">-- Pilih Jabatan Struktural --</option>
              <option v-for="position in props.structuralPositions" :key="position.id" :value="position.id">
                {{ position.jabatan_struktural }}
              </option>
            </select>
          </div>
          <div>
            <label for="functional_position" class="block text-sm font-medium text-gray-700">Jabatan Fungsional</label>
            <select
              id="functional_position_id"
              v-model="form.functional_position_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">-- Pilih Jabatan Fungsional --</option>
              <option v-for="position in props.functionalPositions" :key="position.id" :value="position.id">
                {{ position.jabatan_fungsional }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <label for="eselon" class="block text-sm font-medium text-gray-700">Eselon</label>
          <select
            id="eselon_id"
            v-model="form.eselon_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">-- Pilih Eselon --</option>
            <option v-for="eselon in props.eselons" :key="eselon.id" :value="eselon.id">
              {{ eselon.eselon }}
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="jamsostek" class="block text-sm font-medium text-gray-700">No. Jamsostek</label>
            <input
              type="text"
              id="jamsostek"
              v-model="form.jamsostek"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label for="dplk" class="block text-sm font-medium text-gray-700">No. DPLK</label>
            <input
              type="text"
              id="dplk"
              v-model="form.dplk"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label for="inhealth" class="block text-sm font-medium text-gray-700">No. Inhealth</label>
            <input
              type="text"
              id="inhealth"
              v-model="form.inhealth"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="join_date" class="block text-sm font-medium text-gray-700">Tanggal Masuk</label>
            <input
              type="date"
              id="join_date"
              v-model="form.join_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label for="contract_end_date" class="block text-sm font-medium text-gray-700">Tanggal Habis Kontrak</label>
            <input
              type="date"
              id="contract_end_date"
              v-model="form.contract_end_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="position_id" class="block text-sm font-medium text-gray-700">Jabatan</label>
            <select
              id="position_id"
              v-model="form.position_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">-- Pilih Jabatan --</option>
              <option v-for="position in props.positions" :key="position.id" :value="position.id">
                {{ position.jabatan }}
              </option>
            </select>
          </div>
          <div>
            <label for="position_date" class="block text-sm font-medium text-gray-700">Tanggal Jabatan</label>
            <input
              type="date"
              id="position_date"
              v-model="form.position_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>

        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">Status Keaktifan</label>
          <select
            id="status"
            v-model="form.status"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">-- Pilih Status Keaktifan --</option>
            <option value="Aktif">Aktif</option>
            <option value="Non Aktif">Non Aktif</option>
          </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="cancelEdit"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Batal
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            :disabled="form.processing"
          >
            <span v-if="form.processing">
              <i class="fas fa-spinner fa-spin"></i> Menyimpan...
            </span>
            <span v-else>
              <i class="fas fa-save"></i> Simpan Perubahan
            </span>
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.profile-picture {
  width: 150px;
  height: 150px;
  overflow: hidden;
  border-radius: 50%;
  border: 3px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
}
</style> 