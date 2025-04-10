<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const formData = ref({
    photo: null,
    photoPreview: null,
    nip: '',
    entry_level: '',
    initial: '',
    name: '',
    gender: '',
    birth_place: '',
    birth_date: '',
    age: '',
    address: '',
    province_id: '',
    city_id: '',
    district_id: '',
    temporary_address: '',
    email: '',
    home_phone: '',
    mobile_phone: '',
    fax: '',
    ktp: '',
    npwp: '',
    department_id: '',
    bank_id: '',
    account_number: '',
    jamsostek: '',
    dplk: '',
    inhealth: '',
    religion: '',
    employee_type: '',
    grade: '',
    functional_position: '',
    structural_position: '',
    sub_department: '',
    eselon: '',
    marital_status: '',
    employee_status: '',
    join_date: '',
    contract_end_date: '',
    education: '',
    position_date: '',
    position: '',
    status: ''
});

const provinces = ref([]);
const cities = ref([]);
const districts = ref([]);
const departments = ref([]);
const banks = ref([]);
const religions = ref([
    { id: 1, name: 'Islam' },
    { id: 2, name: 'Kristen' },
    { id: 3, name: 'Katolik' },
    { id: 4, name: 'Hindu' },
    { id: 5, name: 'Budha' },
    { id: 6, name: 'Konghucu' }
]);
const employeeTypes = ref([
    { id: 1, name: 'PNS' },
    { id: 2, name: 'CPNS' },
    { id: 3, name: 'Kontrak' }
]);
const maritalStatuses = ref([
    { id: 1, name: 'Belum Kawin' },
    { id: 2, name: 'Kawin' },
    { id: 3, name: 'Cerai Hidup' },
    { id: 4, name: 'Cerai Mati' }
]);
const employeeStatuses = ref([
    { id: 1, name: 'Aktif' },
    { id: 2, name: 'Non Aktif' },
    { id: 3, name: 'Cuti' }
]);
const educations = ref([
    { id: 1, name: 'SD' },
    { id: 2, name: 'SMP' },
    { id: 3, name: 'SMA' },
    { id: 4, name: 'D3' },
    { id: 5, name: 'S1' },
    { id: 6, name: 'S2' },
    { id: 7, name: 'S3' }
]);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    formData.value.photo = file;

    // Create preview URL
    if (file) {
        formData.value.photoPreview = URL.createObjectURL(file);
    } else {
        formData.value.photoPreview = null;
    }
};

const submitForm = () => {
    const formDataToSubmit = new FormData();
    Object.keys(formData.value).forEach(key => {
        formDataToSubmit.append(key, formData.value[key]);
    });

    router.post('/admin/employees/store', formDataToSubmit, {
        onSuccess: () => {
            router.visit('/admin/employees');
        }
    });
};
</script>

<template>
    <Head title="Tambah Karyawan" />

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
                    <li class="flex items-center text-gray-700 font-semibold">
                        Tambah Karyawan
                    </li>
                </ol>
            </nav>

            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Tambah Karyawan</h1>
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
                                v-if="formData.photoPreview"
                                :src="formData.photoPreview"
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
                        v-model="formData.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    />
                </div>

                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                    <input
                        type="text"
                        id="nip"
                        v-model="formData.nip"
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
                                v-model="formData.gender"
                                value="L"
                                class="form-radio text-blue-600"
                            />
                            <span class="ml-2">Laki-Laki</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input
                                type="radio"
                                v-model="formData.gender"
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
                            v-model="formData.birth_place"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input
                            type="date"
                            id="birth_date"
                            v-model="formData.birth_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <div>
                    <label for="religion" class="block text-sm font-medium text-gray-700">Agama</label>
                    <select
                        id="religion"
                        v-model="formData.religion"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">-- Pilih Agama --</option>
                        <option v-for="religion in religions" :key="religion.id" :value="religion.id">
                            {{ religion.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="marital_status" class="block text-sm font-medium text-gray-700">Status Kawin</label>
                    <select
                        id="marital_status"
                        v-model="formData.marital_status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                        <option value="">-- Status Kawin --</option>
                        <option v-for="status in maritalStatuses" :key="status.id" :value="status.id">
                            {{ status.name }}
                        </option>
                    </select>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea
                            id="address"
                            v-model="formData.address"
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
                                v-model="formData.province_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">--Pilih Provinsi--</option>
                                <option v-for="province in provinces" :key="province.id" :value="province.id">
                                    {{ province.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="city_id" class="block text-sm font-medium text-gray-700">Kabupaten/Kota</label>
                            <select
                                id="city_id"
                                v-model="formData.city_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">--Pilih Kabupaten/Kota--</option>
                                <option v-for="city in cities" :key="city.id" :value="city.id">
                                    {{ city.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="district_id" class="block text-sm font-medium text-gray-700">Kecamatan</label>
                            <select
                                id="district_id"
                                v-model="formData.district_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">--Pilih Kecamatan--</option>
                                <option v-for="district in districts" :key="district.id" :value="district.id">
                                    {{ district.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            type="email"
                            id="email"
                            v-model="formData.email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <div>
                        <label for="home_phone" class="block text-sm font-medium text-gray-700">Tlp. Rumah</label>
                        <input
                            type="tel"
                            id="home_phone"
                            v-model="formData.home_phone"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="(021) 999-9999"
                        />
                    </div>
                    <div>
                        <label for="mobile_phone" class="block text-sm font-medium text-gray-700">No. HP</label>
                        <input
                            type="tel"
                            id="mobile_phone"
                            v-model="formData.mobile_phone"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Isikan No Hp"
                        />
                    </div>
                    <div>
                        <label for="fax" class="block text-sm font-medium text-gray-700">No. Fax</label>
                        <input
                            type="tel"
                            id="fax"
                            v-model="formData.fax"
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
                            v-model="formData.ktp"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <div>
                        <label for="npwp" class="block text-sm font-medium text-gray-700">NPWP</label>
                        <input
                            type="text"
                            id="npwp"
                            v-model="formData.npwp"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="bank_id" class="block text-sm font-medium text-gray-700">Bank Transfer</label>
                        <select
                            id="bank_id"
                            v-model="formData.bank_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Pilih Bank Transfer --</option>
                            <option v-for="bank in banks" :key="bank.id" :value="bank.id">
                                {{ bank.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="account_number" class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                        <input
                            type="text"
                            id="account_number"
                            v-model="formData.account_number"
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
                            v-model="formData.department_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Bagian --</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="sub_department" class="block text-sm font-medium text-gray-700">Sub/Bagian</label>
                        <select
                            id="sub_department"
                            v-model="formData.sub_department"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                            <option value="">-- Pilih Sub Bagian --</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="employee_type" class="block text-sm font-medium text-gray-700">Jenis Pegawai</label>
                    <select
                        id="employee_type"
                        v-model="formData.employee_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                        <option value="">-- Pilih Jenis Pegawai --</option>
                        <option v-for="type in employeeTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="structural_position" class="block text-sm font-medium text-gray-700">Jabatan Struktural</label>
                        <select
                            id="structural_position"
                            v-model="formData.structural_position"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Pilih Jabatan Struktural --</option>
                        </select>
                    </div>
                    <div>
                        <label for="functional_position" class="block text-sm font-medium text-gray-700">Jabatan Fungsional</label>
                        <select
                            id="functional_position"
                            v-model="formData.functional_position"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Pilih Jabatan Fungsional --</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="eselon" class="block text-sm font-medium text-gray-700">Eselon</label>
                    <select
                        id="eselon"
                        v-model="formData.eselon"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                        <option value="">-- Pilih Eselon --</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="jamsostek" class="block text-sm font-medium text-gray-700">No. Jamsostek</label>
                        <input
                            type="text"
                            id="jamsostek"
                            v-model="formData.jamsostek"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label for="dplk" class="block text-sm font-medium text-gray-700">No. DPLK</label>
                        <input
                            type="text"
                            id="dplk"
                            v-model="formData.dplk"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label for="inhealth" class="block text-sm font-medium text-gray-700">No. Inhealth</label>
                        <input
                            type="text"
                            id="inhealth"
                            v-model="formData.inhealth"
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
                            v-model="formData.join_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <div>
                        <label for="contract_end_date" class="block text-sm font-medium text-gray-700">Tanggal Habis Kontrak</label>
                        <input
                            type="date"
                            id="contract_end_date"
                            v-model="formData.contract_end_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Jabatan</label>
                        <select
                            id="position"
                            v-model="formData.position"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">-- Pilih Jabatan --</option>
                        </select>
                    </div>
                    <div>
                        <label for="position_date" class="block text-sm font-medium text-gray-700">Tanggal Jabatan</label>
                        <input
                            type="date"
                            id="position_date"
                            v-model="formData.position_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status Keaktifan</label>
                    <select
                        id="status"
                        v-model="formData.status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">-- Pilih Status Keaktifan --</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.profile-picture {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  overflow: hidden;
  background-color: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 4px solid #f7fafc;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
