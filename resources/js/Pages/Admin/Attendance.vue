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
      data: [95, 93, 97, 94, 96],
    },
    {
      label: 'Keterlambatan',
      backgroundColor: '#FFC107',
      borderColor: '#FFC107',
      data: [5, 7, 3, 6, 4],
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

const openEditModal = (employee) => {
  // Logic to open edit modal
};

const deleteEmployee = (employeeId) => {
  // Logic to delete employee
};
</script>

<template>
  <Head title="Admin Attendance" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Absensi</h2>
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

      <!-- Tabel Karyawan -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Daftar Karyawan</h2>
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 text-left">Nama</th>
              <th class="p-2 text-left">Posisi</th>
              <th class="p-2 text-left">Email</th>
              <th class="p-2">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="employee in employees" :key="employee.id" class="border-b hover:bg-gray-50 transition duration-200">
              <td class="p-2">{{ employee.name }}</td>
              <td class="p-2">{{ employee.position }}</td>
              <td class="p-2">{{ employee.email }}</td>
              <td class="p-2 flex gap-2">
                <button class="text-blue-500 flex items-center" @click="openEditModal(employee)">
                  <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button class="text-red-500 flex items-center" @click="deleteEmployee(employee.id)">
                  <i class="fas fa-trash mr-1"></i> Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
