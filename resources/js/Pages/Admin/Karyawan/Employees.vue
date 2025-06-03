<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Menerima data dari controller
const props = defineProps({
  departments: Array,
});

const divisions = computed(() => props.departments);
const selectedDivision = ref(null);
const searchQuery = ref('');

// Tambahan state untuk pagination
const currentPage = ref(1);
const itemsPerPage = 7; // Fixed items per page

// Computed property untuk total items
const totalItems = computed(() => {
  return filteredEmployees.value.length;
});

// Computed property untuk total halaman
const totalPages = computed(() => {
  return Math.ceil(totalItems.value / itemsPerPage);
});

// Computed property untuk range items yang ditampilkan
const displayedItemsRange = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage + 1;
  const end = Math.min(currentPage.value * itemsPerPage, totalItems.value);
  return `${start}-${end}`;
});

// Computed property untuk employees yang ditampilkan sesuai pagination
const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredEmployees.value.slice(start, end);
});

// Fungsi untuk mengubah halaman
const changePage = (page) => {
  currentPage.value = page;
};

const filteredEmployees = computed(() => {
  if (!selectedDivision.value) return [];
  if (!searchQuery.value) return selectedDivision.value.employees;

  const query = searchQuery.value.toLowerCase();
  return selectedDivision.value.employees.filter(employee => {
    // NIP dan Nama
    const nip = employee.nip?.toLowerCase() || '';
    const name = employee.name?.toLowerCase() || '';
    // Jabatan (fungsional, struktural, posisi)
    const jabatanFungsional = employee.functional_position?.jabatan_fungsional?.toLowerCase() || employee.functionalPosition?.jabatan_fungsional?.toLowerCase() || '';
    const jabatanStruktural = employee.structural_position?.jabatan_struktural?.toLowerCase() || employee.structuralPosition?.jabatan_struktural?.toLowerCase() || '';
    const jabatanLain = employee.position?.jabatan?.toLowerCase() || '';
    // Sub Bagian
    const subBagian = employee.subDepartmentName?.toLowerCase() || employee.subDepartment?.subbagian?.toLowerCase() || employee.sub_department?.subbagian?.toLowerCase() || employee.debug_subbagian?.toLowerCase() || '';
    // Jenis Pegawai
    const jenisPegawai = employee.employee_type?.status_pegawai?.toLowerCase() || employee.employeeType?.status_pegawai?.toLowerCase() || '';
    // Cek semua kemungkinan
    return (
      nip.includes(query) ||
      name.includes(query) ||
      jabatanFungsional.includes(query) ||
      jabatanStruktural.includes(query) ||
      jabatanLain.includes(query) ||
      subBagian.includes(query) ||
      jenisPegawai.includes(query)
    );
  });
});

const selectDivision = (division) => {
  selectedDivision.value = division;
  currentPage.value = 1; // Reset ke halaman pertama saat ganti divisi
  
  // Debug: Log data karyawan pertama setelah memilih divisi
  if (division.employees && division.employees.length > 0) {
    console.log('First employee data:', division.employees[0]);
  }
};

const openAddModal = () => {
  router.visit(route('admin.employees.create'));
};

const openEditModal = (employee) => {
  router.visit(route('admin.employees.edit', { id: employee.id }));
};

const deleteEmployee = (employeeId) => {
  if (confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
    router.delete(`/admin/employees/${employeeId}`, {
      onSuccess: () => {
        // Refresh halaman setelah berhasil menghapus
        selectedDivision.value.employees = selectedDivision.value.employees.filter(emp => emp.id !== employeeId);
      }
    });
  }
};

// Debug: Lihat data setelah komponen dimuat
onMounted(() => {
  if (props.departments && props.departments.length > 0) {
    console.log('All departments:', props.departments);
    
    // Cek struktur data karyawan pada departemen pertama
    const firstDeptWithEmployees = props.departments.find(dept => dept.employees && dept.employees.length > 0);
    if (firstDeptWithEmployees) {
      console.log('Sample employee data structure:', firstDeptWithEmployees.employees[0]);
      
      // Debug khusus untuk melihat properti sub bagian
      const employee = firstDeptWithEmployees.employees[0];
      console.log('Sub Bagian Debug:');
      console.log('- sub_department_id:', employee.sub_department_id);
      console.log('- subDepartment:', employee.subDepartment);
      console.log('- sub_department:', employee.sub_department);
      
      // Debugging properti lain untuk perbandingan
      console.log('Properti relasi lain:');
      console.log('- department_id:', employee.department_id);
      console.log('- department:', employee.department);
      console.log('- employeeType:', employee.employeeType);
      console.log('- functionalPosition:', employee.functionalPosition);
    }
  }
});

const showImportModal = ref(false);
const importForm = useForm({ file: null });

const downloadTemplate = () => {
  window.location.href = route('admin.employees.template');
};

const submitImport = () => {
  importForm.post(route('admin.employees.import'), {
    onSuccess: () => {
      showImportModal.value = false;
      importForm.reset();
    }
  });
};
</script>

<template>
  <Head title="Admin Employees"/>

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
          <li class="flex items-center text-gray-700 font-semibold">
            Karyawan
          </li>
        </ol>
      </nav>

      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Karyawan</h1>
      </div>
    </template>

    <div v-if="$page.props.flash && $page.props.flash.message" :class="{
      'bg-green-100 text-green-800': $page.props.flash?.type === 'success',
      'bg-red-100 text-red-800': $page.props.flash?.type === 'error'
    }" class="p-3 rounded mb-4">
      {{ $page.props.flash.message }}
    </div>

    <div class="flex gap-2 mb-4">
      <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center" @click="showImportModal = true">
        <i class="fas fa-upload mr-2"></i> Unggah Massal
      </button>
      <a :href="route('admin.employees.export')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
        <i class="fas fa-file-excel mr-2"></i> Export Excel
      </a>
      <div class="relative">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Cari karyawan..."
          class="pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
      </div>
      <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center" @click="openAddModal">
        <i class="fas fa-plus mr-2"></i> Tambah Karyawan
      </button>
    </div>

    <div class="flex gap-6 mt-6">
      <!-- Bagian -->
      <div class="w-1/4 bg-white p-4 rounded-lg shadow">
        <h2 class="font-semibold text-lg mb-2">Bagian</h2>
        <div
          v-for="division in divisions"
          :key="division.id"
          class="p-3 cursor-pointer rounded-lg transition duration-200 flex items-center justify-between"
          :class="{ 'bg-blue-600 text-white': selectedDivision?.id === division.id, 'hover:bg-gray-100': selectedDivision?.id !== division.id }"
          @click="selectDivision(division)"
        >
          <span class="flex items-center">
            <i class="fas fa-building mr-2"></i>
            {{ division.name }}
          </span>
          <span class="bg-black/10 px-2 py-0.5 text-xs rounded-full" :class="{ 'bg-white/20 text-white': selectedDivision?.id === division.id }">
            {{ division.employees.length }}
          </span>
        </div>
      </div>

      <!-- Karyawan -->
      <div class="flex-1 bg-white p-6 rounded-lg shadow">
        <div v-if="selectedDivision">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">{{ selectedDivision.name }}</h2>
          </div>

          <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-2">NIP</th>
                <th class="p-2">Nama</th>
                <th class="p-2">Jabatan</th>
                <th class="p-2">Sub Bagian</th>
                <th class="p-2">Jenis Pegawai</th>
                <th class="p-2 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="employee in paginatedEmployees"
                :key="employee.id"
                class="border-b hover:bg-gray-50 transition duration-200"
              >
                <td class="p-2">{{ employee.nip || '-' }}</td>
                <td class="p-2">{{ employee.name || '-' }}</td>
                <!-- Percobaan beberapa kemungkinan atribut jabatan -->
                <td class="p-2">
                  {{ employee.functional_position?.jabatan_fungsional || 
                     employee.functionalPosition?.jabatan_fungsional || 
                     employee.structural_position?.jabatan_struktural || 
                     employee.structuralPosition?.jabatan_struktural || 
                     employee.position?.jabatan || 
                     '-' }}
                </td>
                <!-- Perbaikan akses data Sub Bagian -->
                <td class="p-2">
                  {{ employee.subDepartmentName || 
                     employee.subDepartment?.subbagian || 
                     employee.sub_department?.subbagian || 
                     employee.debug_subbagian ||
                     (employee.sub_department_id ? `ID: ${employee.sub_department_id}` : '-') }}
                </td>
                <!-- Percobaan beberapa kemungkinan atribut jenis pegawai -->
                <td class="p-2">
                  {{ employee.employee_type?.status_pegawai || 
                     employee.employeeType?.status_pegawai || 
                     '-' }}
                </td>
                <td class="p-2 text-center">
                  <button class="text-blue-600 hover:underline mr-2" @click="openEditModal(employee)">
                    <i class="fas fa-edit mr-1"></i> Edit
                  </button>
                  <button class="text-red-600 hover:underline" @click="deleteEmployee(employee.id)">
                    <i class="fas fa-trash mr-1"></i> Hapus
                  </button>
                </td>
              </tr>
              <tr v-if="paginatedEmployees.length === 0">
                <td colspan="6" class="p-4 text-center text-gray-500">
                  <div class="py-4">
                    <i class="fas fa-folder-open text-gray-300 text-3xl mb-2"></i>
                    <p>Belum ada karyawan di bagian ini</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination dan Info -->
          <div v-if="paginatedEmployees.length > 0" class="mt-4 flex items-center justify-between border-t pt-4">
            <div class="text-sm text-gray-700">
              Menampilkan {{ displayedItemsRange }} dari {{ totalItems }} karyawan
            </div>

            <div class="flex items-center space-x-2">
              <button 
                @click="changePage(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded border text-sm"
                :class="currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'"
              >
                <i class="fas fa-chevron-left"></i>
              </button>

              <template v-for="page in totalPages" :key="page">
                <button 
                  v-if="page === currentPage || 
                        page === 1 || 
                        page === totalPages || 
                        (page >= currentPage - 1 && page <= currentPage + 1)"
                  @click="changePage(page)"
                  class="px-3 py-1 rounded text-sm"
                  :class="currentPage === page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100'"
                >
                  {{ page }}
                </button>
                <span 
                  v-else-if="page === currentPage - 2 || page === currentPage + 2" 
                  class="text-gray-400"
                >
                  ...
                </span>
              </template>

              <button 
                @click="changePage(currentPage + 1)" 
                :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border text-sm"
                :class="currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'"
              >
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-10">
          <p>Pilih bagian untuk melihat daftar karyawan</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>

  <!-- Modal Import -->
  <div v-if="showImportModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
      <h2 class="text-lg font-semibold mb-4">Unggah Data Karyawan (Excel)</h2>
      <div class="flex flex-col gap-3">
        <button @click="downloadTemplate" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded flex items-center">
          <i class="fas fa-download mr-2"></i> Download Template
        </button>
        <form @submit.prevent="submitImport" enctype="multipart/form-data">
          <input
            type="file"
            id="import-file"
            name="file"
            @change="e => importForm.file = e.target.files[0]"
            accept=".xls,.xlsx"
            class="mb-2"
            required
          >
          <div class="flex gap-2 mt-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded" :disabled="importForm.processing">
              <i class="fas fa-upload mr-2"></i> Unggah File
            </button>
            <button type="button" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded" @click="showImportModal = false">Batal</button>
          </div>
        </form>
        <div v-if="importForm.errors.file" class="text-red-600 text-sm mt-2">{{ importForm.errors.file }}</div>
      </div>
    </div>
  </div>
</template> 