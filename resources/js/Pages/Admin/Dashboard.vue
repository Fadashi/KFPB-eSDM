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
</script>

<template>
  <Head title="Admin Dashboard" />

  <div class="flex">
    <!-- Sidebar -->
    <Sidebar @sidebar-toggle="handleSidebarCollapse" />

    <div class="flex-1 transition-all duration-300" :class="sidebarCollapsed ? 'ml-[70px]' : 'ml-[250px]'">
      <!-- Topbar -->
      <Topbar />

      <!-- Page Content -->
      <div class="min-h-screen bg-gray-100 p-6 space-y-6">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Admin Dashboard</h2>

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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Grafik Kehadiran Minggu Ini</h2>
            <div class="h-80">
              <Line :data="chartData" :options="chartOptions" />
            </div>
          </div>

          <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Aktivitas Terbaru</h2>
            <div class="space-y-4">
              <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start gap-4">
                <div class="rounded-full p-2" :class="{
                  'bg-green-100': activity.type === 'new_employee',
                  'bg-blue-100': activity.type === 'leave_request',
                  'bg-yellow-100': activity.type === 'attendance'
                }">
                  <i class="fas" :class="{
                    'fa-user-plus text-green-600': activity.type === 'new_employee',
                    'fa-calendar-check text-blue-600': activity.type === 'leave_request',
                    'fa-clock text-yellow-600': activity.type === 'attendance'
                  }"></i>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-600">{{ activity.message }}</p>
                  <span class="text-xs text-gray-400">{{ activity.time }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
