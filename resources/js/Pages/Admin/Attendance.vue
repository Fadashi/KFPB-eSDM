<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import Sidebar from '@/Components/Sidebar.vue';
import Topbar from '@/Components/Topbar.vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

// Props dari controller
const props = defineProps({
  statistics: Object,
  chartData: Object,
  attendanceData: Object,
});

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

// Tambahan state untuk pagination
const currentPage = ref(1);
const perPage = ref(10);
const perPageOptions = [5, 10, 20, 50];
const activeFilter = ref('hadir');

// Menyiapkan data yang akan ditampilkan berdasarkan filter
const filteredEmployees = ref([]);

// Computed property untuk total item dari data yang diterima dari controller
const totalItems = computed(() => {
  return props.attendanceData && props.attendanceData[activeFilter.value] ? 
    props.attendanceData[activeFilter.value].length : 0;
});

// Computed property untuk total halaman
const totalPages = computed(() => {
  return Math.ceil(totalItems.value / perPage.value);
});

// Computed property untuk range items yang ditampilkan
const displayedItemsRange = computed(() => {
  const start = (currentPage.value - 1) * perPage.value + 1;
  const end = Math.min(currentPage.value * perPage.value, totalItems.value);
  return `${start}-${end}`;
});

// Fungsi untuk mengubah filter
const setFilter = (filter) => {
  activeFilter.value = filter;
  currentPage.value = 1; // Reset halaman ke 1 saat ganti filter
  updateFilteredEmployees();
};

// Fungsi untuk mengupdate data yang ditampilkan
const updateFilteredEmployees = () => {
  const startIndex = (currentPage.value - 1) * perPage.value;
  const endIndex = startIndex + perPage.value;
  
  if (props.attendanceData && props.attendanceData[activeFilter.value]) {
    filteredEmployees.value = props.attendanceData[activeFilter.value].slice(startIndex, endIndex);
  } else {
    filteredEmployees.value = [];
  }
};

// Fungsi untuk mengubah halaman
const changePage = (page) => {
  currentPage.value = page;
  updateFilteredEmployees();
};

// Fungsi untuk mengubah jumlah item per halaman
const changePerPage = (value) => {
  perPage.value = parseInt(value);
  currentPage.value = 1; // Reset ke halaman pertama
  updateFilteredEmployees();
};

// Inisialisasi data awal
setFilter('hadir');
</script>

<template>
  <Head title="Admin Attendance" />

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
                        Absensi
                    </li>
                </ol>
            </nav>

            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Absensi</h1>
            </div>
        </template>

    <div class="p-6 space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="text-3xl text-indigo-600 fas fa-users"></i>
          <div>
            <h3 class="text-sm text-gray-600">Total Karyawan</h3>
            <p class="text-2xl font-semibold text-gray-800">{{ statistics.totalEmployees }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="text-3xl text-indigo-600 fas fa-check-circle"></i>
          <div>
            <h3 class="text-sm text-gray-600">Hadir Hari Ini</h3>
            <p class="text-2xl font-semibold text-gray-800">{{ statistics.presentToday }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="text-3xl text-indigo-600 fas fa-clock"></i>
          <div>
            <h3 class="text-sm text-gray-600">Terlambat</h3>
            <p class="text-2xl font-semibold text-gray-800">{{ statistics.late }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="text-3xl text-indigo-600 fas fa-calendar-minus"></i>
          <div>
            <h3 class="text-sm text-gray-600">Izin/Cuti</h3>
            <p class="text-2xl font-semibold text-gray-800">{{ statistics.onLeave }}</p>
          </div>
        </div>
      </div>

      <!-- Grafik Kehadiran -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Grafik Kehadiran Minggu Ini</h2>
        <div class="h-80">
          <Line :data="chartData" :options="{ responsive: true, maintainAspectRatio: false }" />
        </div>
      </div>

      <!-- Filter dan Tabel Kehadiran -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex space-x-4 mb-6">
          <button 
            @click="setFilter('hadir')"
            :class="[
              'flex-1 py-3 px-4 rounded-lg font-medium transition-all duration-200',
              activeFilter === 'hadir' 
                ? 'bg-green-500 text-white shadow-md' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            <i class="fas fa-check-circle mr-2"></i>
            Hadir
          </button>
          <button 
            @click="setFilter('izin')"
            :class="[
              'flex-1 py-3 px-4 rounded-lg font-medium transition-all duration-200',
              activeFilter === 'izin' 
                ? 'bg-blue-500 text-white shadow-md' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            <i class="fas fa-calendar-minus mr-2"></i>
            Izin/Cuti
          </button>
          <button 
            @click="setFilter('terlambat')"
            :class="[
              'flex-1 py-3 px-4 rounded-lg font-medium transition-all duration-200',
              activeFilter === 'terlambat' 
                ? 'bg-yellow-500 text-white shadow-md' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            <i class="fas fa-clock mr-2"></i>
            Terlambat
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-2 text-left">Nama</th>
                <th class="p-2 text-left">Posisi</th>
                <th class="p-2 text-left">Email</th>
                <th v-if="activeFilter === 'hadir' || activeFilter === 'terlambat'" class="p-2 text-left">Waktu</th>
                <th v-if="activeFilter === 'izin'" class="p-2 text-left">Alasan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in filteredEmployees" :key="employee.id" class="border-b hover:bg-gray-50 transition duration-200">
                <td class="p-2">{{ employee.name }}</td>
                <td class="p-2">{{ employee.position }}</td>
                <td class="p-2">{{ employee.email }}</td>
                <td v-if="activeFilter === 'hadir' || activeFilter === 'terlambat'" class="p-2">
                  <span :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    activeFilter === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ]">
                    {{ employee.time }}
                  </span>
                </td>
                <td v-if="activeFilter === 'izin'" class="p-2">
                  <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ employee.reason }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

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
      </div>
      
    </div>
  </AuthenticatedLayout>
</template>
