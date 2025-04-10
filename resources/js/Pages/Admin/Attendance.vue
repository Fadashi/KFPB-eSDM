<script setup>
import { ref } from 'vue';
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

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

// Data statistik
const statistics = ref({
  totalEmployees: 50,
  presentToday: 45,
  late: 2,
  onLeave: 3
});

// Data untuk grafik
const chartData = {
  labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
  datasets: [
    {
      label: 'Kehadiran',
      backgroundColor: '#4CAF50',
      borderColor: '#4CAF50',
      data: [45, 43, 47, 44, 46],
    },
    {
      label: 'Keterlambatan',
      backgroundColor: '#FFC107',
      borderColor: '#FFC107',
      data: [5, 7, 3, 6, 4],
    },
    {
      label: 'Cuti/Izin',
      backgroundColor: '#2196F3',
      borderColor: '#2196F3',
      data: [3, 2, 4, 1, 2],
    },
  ],
};

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
};

// Data aktivitas terbaru
const recentActivities = ref([
  { id: 1, type: 'new_employee', message: 'Karyawan baru ditambahkan: Budi Santoso', time: '5 menit yang lalu' },
  { id: 2, type: 'leave_request', message: 'Pengajuan cuti dari Ani Wijaya', time: '10 menit yang lalu' },
  { id: 3, type: 'attendance', message: '45 karyawan telah melakukan absensi', time: '30 menit yang lalu' },
]);

// Dummy data for employees
const employees = ref([
  { id: 1, name: 'Budi Santoso', position: 'Frontend Developer', email: 'budi@example.com' },
  { id: 2, name: 'Dedi Kurniawan', position: 'Backend Developer', email: 'dedi@example.com' },
  { id: 3, name: 'Rini Susanti', position: 'UI/UX Designer', email: 'rini@example.com' },
]);

// State untuk filter dan data
const activeFilter = ref('hadir');
const filteredEmployees = ref([]);

// Data dummy untuk setiap kategori
const attendanceData = {
  hadir: [
    { id: 1, name: 'Budi Santoso', position: 'Frontend Developer', email: 'budi@example.com', time: '08:00' },
    { id: 2, name: 'Dedi Kurniawan', position: 'Backend Developer', email: 'dedi@example.com', time: '08:05' },
    { id: 3, name: 'Rini Susanti', position: 'UI/UX Designer', email: 'rini@example.com', time: '07:55' },
  ],
  izin: [
    { id: 4, name: 'Ahmad Fauzi', position: 'Project Manager', email: 'ahmad@example.com', reason: 'Cuti Tahunan' },
    { id: 5, name: 'Siti Rahayu', position: 'HR Manager', email: 'siti@example.com', reason: 'Izin Pribadi' },
  ],
  terlambat: [
    { id: 6, name: 'Joko Widodo', position: 'System Analyst', email: 'joko@example.com', time: '09:15' },
    { id: 7, name: 'Dewi Lestari', position: 'QA Engineer', email: 'dewi@example.com', time: '09:30' },
  ],
};

// Fungsi untuk mengubah filter
const setFilter = (filter) => {
  activeFilter.value = filter;
  filteredEmployees.value = attendanceData[filter];
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
          <Line :data="chartData" :options="chartOptions" />
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
        </div>
      </div>
      
    </div>
  </AuthenticatedLayout>
</template>
