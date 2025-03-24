<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
)

// Data statistik
const statistics = ref({
  totalEmployees: 50,
  presentToday: 45,
  late: 2,
  onLeave: 3
})

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
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
}

// Data aktivitas terbaru
const recentActivities = ref([
  { id: 1, type: 'new_employee', message: 'Karyawan baru ditambahkan: Budi Santoso', time: '5 menit yang lalu' },
  { id: 2, type: 'leave_request', message: 'Pengajuan cuti dari Ani Wijaya', time: '10 menit yang lalu' },
  { id: 3, type: 'attendance', message: '45 karyawan telah melakukan absensi', time: '30 menit yang lalu' },
])
</script>

<template>
  <Head title="Admin Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Admin Dashboard
      </h2>
    </template>

    <div class="dashboard">
      <!-- Statistik Cards -->
      <div class="stats-container">
        <div class="stat-card">
          <i class="fas fa-users"></i>
          <div class="stat-info">
            <h3>Total Karyawan</h3>
            <p>{{ statistics.totalEmployees }}</p>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-check-circle"></i>
          <div class="stat-info">
            <h3>Hadir Hari Ini</h3>
            <p>{{ statistics.presentToday }}</p>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-clock"></i>
          <div class="stat-info">
            <h3>Terlambat</h3>
            <p>{{ statistics.late }}</p>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-calendar-minus"></i>
          <div class="stat-info">
            <h3>Izin/Cuti</h3>
            <p>{{ statistics.onLeave }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Grafik Absensi -->
        <div class="chart-container">
          <h2 class="text-lg font-semibold mb-4">Grafik Kehadiran Minggu Ini</h2>
          <div class="chart-wrapper h-80">
            <Line :data="chartData" :options="chartOptions" />
          </div>
        </div>

        <!-- Aktivitas Terbaru -->
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
  </AuthenticatedLayout>
</template>

<style scoped>
.dashboard {
  @apply p-6 space-y-6;
}

.stats-container {
  @apply grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4;
}

.stat-card {
  @apply bg-white p-6 rounded-lg shadow flex items-center gap-4;
}

.stat-card i {
  @apply text-3xl text-indigo-600;
}

.stat-info h3 {
  @apply text-sm text-gray-600;
}

.stat-info p {
  @apply text-2xl font-semibold text-gray-800;
}

.chart-container {
  @apply bg-white p-6 rounded-lg shadow;
}
</style>
