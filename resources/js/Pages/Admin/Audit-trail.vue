<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Data dummy untuk audit trail
const auditLogs = ref([
  { 
    id: 1, 
    timestamp: '2023-10-01 10:00', 
    user: {
      name: 'John Doe',
      role: 'admin'
    },
    action: 'Tambah', 
    data: 'Karyawan Baru', 
    old_value: '-',
    new_value: 'John Doe'
  },
  { 
    id: 2, 
    timestamp: '2023-10-02 11:30', 
    user: {
      name: 'Jane Smith',
      role: 'supervisor'
    },
    action: 'Update', 
    data: 'Data Jabatan',
    old_value: 'Staff IT',
    new_value: 'Senior IT'
  },
  { 
    id: 3, 
    timestamp: '2023-10-03 09:15', 
    user: {
      name: 'Mike Wilson',
      role: 'manager'
    },
    action: 'Hapus', 
    data: 'Data Karyawan',
    old_value: 'Jane Smith',
    new_value: '-'
  },
  { 
    id: 4, 
    timestamp: '2023-10-04 14:20', 
    user: {
      name: 'Sarah Johnson',
      role: 'admin'
    },
    action: 'Update', 
    data: 'User Profile',
    old_value: 'email: old@mail.com',
    new_value: 'email: new@mail.com'
  },
  { 
    id: 5, 
    timestamp: '2023-10-05 16:45', 
    user: {
      name: 'Robert Lee',
      role: 'admin'
    },
    action: 'Reset', 
    data: 'User Account',
    old_value: '********',
    new_value: 'Password baru telah dikirim ke email'
  },
]);

// State untuk filter dan pencarian
const searchQuery = ref('');
const filterAction = ref('all');

// State untuk pagination
const currentPage = ref(1);
const perPage = ref(10);
const perPageOptions = [5, 10, 20, 50];

// Computed property untuk filtered logs
const filteredLogs = computed(() => {
  return auditLogs.value.filter(log => {
    const matchesSearch = 
      log.id.toString().includes(searchQuery.value.toLowerCase()) ||
      log.timestamp.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.user.role.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.action.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.data.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.old_value.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.new_value.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesAction = filterAction.value === 'all' || log.action === filterAction.value;
    return matchesSearch && matchesAction;
  });
});

// Computed property untuk total items
const totalItems = computed(() => {
  return filteredLogs.value.length;
});

// Computed property untuk total halaman
const totalPages = computed(() => {
  return Math.ceil(filteredLogs.value.length / perPage.value);
});

// Computed property untuk range items yang ditampilkan
const displayedItemsRange = computed(() => {
  const start = (currentPage.value - 1) * perPage.value + 1;
  const end = Math.min(currentPage.value * perPage.value, totalItems.value);
  return `${start}-${end}`;
});

// Computed property untuk logs yang ditampilkan sesuai pagination
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredLogs.value.slice(start, end);
});

// Fungsi untuk mengubah halaman
const changePage = (page) => {
  currentPage.value = page;
};

// Fungsi untuk mengubah jumlah item per halaman
const changePerPage = (value) => {
  perPage.value = parseInt(value);
  currentPage.value = 1; // Reset ke halaman pertama
};
</script>

<template>
  <Head title="Admin Audit Trail" />

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
            Audit Trail
          </li>
        </ol>
      </nav>

      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Audit Trail</h1>
      </div>
    </template>

    <div class="bg-white p-6 rounded-lg shadow mt-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 space-y-4 md:space-y-0">
        <h2 class="text-lg font-semibold">Riwayat Audit</h2>
        <div class="flex flex-col md:flex-row gap-4">
          <div class="relative">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari riwayat audit..."
              class="w-full md:w-64 pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <div class="absolute left-3 top-2.5 text-gray-400">
              <i class="fas fa-search"></i>
            </div>
          </div>
          <select
            v-model="filterAction"
            class="w-full md:w-48 px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="all">Semua Aksi</option>
            <option value="Tambah">Tambah</option>
            <option value="Update">Update</option>
            <option value="Hapus">Hapus</option>
            <option value="Reset">Reset</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full border-collapse">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 text-left">No</th>
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">Waktu</th>
              <th class="p-2 text-left">Nama</th>
              <th class="p-2 text-left">Role</th>
              <th class="p-2 text-left">Aksi</th>
              <th class="p-2 text-left">Data</th>
              <th class="p-2 text-left">Value Asal</th>
              <th class="p-2 text-left">Value Baru</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(log, index) in paginatedLogs"
              :key="log.id"
              class="border-b hover:bg-gray-50 transition duration-200"
            >
              <td class="p-2 text-sm">{{ (currentPage - 1) * perPage + index + 1 }}</td>
              <td class="p-2 text-sm">{{ log.id }}</td>
              <td class="p-2 text-sm">{{ log.timestamp }}</td>
              <td class="p-2 text-sm">{{ log.user.name }}</td>
              <td class="p-2 text-sm">{{ log.user.role }}</td>
              <td class="p-2">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': log.action === 'Tambah',
                    'bg-blue-100 text-blue-800': log.action === 'Update',
                    'bg-red-100 text-red-800': log.action === 'Hapus',
                    'bg-purple-100 text-purple-800': log.action === 'Reset'
                  }"
                >
                  {{ log.action }}
                </span>
              </td>
              <td class="p-2 text-sm">{{ log.data }}</td>
              <td class="p-2 text-sm">{{ log.old_value }}</td>
              <td class="p-2 text-sm">{{ log.new_value }}</td>
            </tr>
            <tr v-if="paginatedLogs.length === 0">
              <td colspan="9" class="p-2 text-center text-sm text-gray-500">
                Tidak ada riwayat audit yang ditemukan
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-700">Tampilkan</span>
          <div class="relative">
            <select
              :value="perPage"
              @change="changePerPage($event.target.value)"
              class="border rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white appearance-none pr-8 cursor-pointer"
            >
              <option v-for="option in perPageOptions" :key="option" :value="option" class="py-1">
                {{ option }}
              </option>
            </select>
          </div>
          <span class="text-sm text-gray-700">
            item per halaman | Menampilkan {{ displayedItemsRange }} dari {{ totalItems }} item
          </span>
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
  </AuthenticatedLayout>
</template>
