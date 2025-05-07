<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Line, Pie } from 'vue-chartjs';
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
  Legend,
  ArcElement,
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
  Legend,
  ArcElement
);

// Dapatkan props dari controller
const props = defineProps({
  statistics: Object,
  attendanceChartData: Object,
  departmentChartData: Object,
  leaveRequests: Array,
  announcements: Array
});

const sidebarCollapsed = ref(false);

// Data statistik dari controller
const statistics = ref(props.statistics);

// Data untuk grafik kehadiran dari controller
const attendanceChartData = props.attendanceChartData;

// Data untuk grafik departemen dari controller
const departmentChartData = props.departmentChartData;

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
};

// Data cuti dari controller
const leaveRequests = ref(props.leaveRequests);

// Data pengumuman dari controller
const announcements = ref(props.announcements);

const approveLeave = (requestId) => {
  // Logic to approve leave
};

const rejectLeave = (requestId) => {
  // Logic to reject leave
};
</script>

<template>
  <Head title="Admin Dashboard" />

  <AuthenticatedLayout>
    <template #header>
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center text-gray-700 font-semibold">
                        <i class="fas fa-home text-gray-600 mr-1"></i>
                        <p>Dashboard</p>
                    </li>
                </ol>
            </nav>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
            </div>
        </template>

    <div class="p-6 space-y-8">
      <!-- Statistik Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center">
            <i class="fas fa-users text-3xl text-indigo-600"></i>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Karyawan</p>
              <p class="text-2xl font-semibold">{{ statistics.totalEmployees }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center">
            <i class="fas fa-building text-3xl text-green-600"></i>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Bagian</p>
              <p class="text-2xl font-semibold">{{ statistics.totalDepartments }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center">
            <i class="fas fa-sitemap text-3xl text-blue-600"></i>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Sub Bagian</p>
              <p class="text-2xl font-semibold">{{ statistics.totalSubDepartments }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Grafik Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">Kehadiran Mingguan</h3>
          <div class="h-64">
            <Line :data="attendanceChartData" :options="chartOptions" />
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">Distribusi Departemen</h3>
          <div class="h-64">
            <Pie :data="departmentChartData" :options="chartOptions" />
          </div>
        </div>
      </div>

      <!-- Statistik Panel -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center">
            <div class="rounded-full p-3 bg-blue-100">
              <i class="fas fa-user-check text-xl text-blue-600"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Hadir Hari Ini</p>
              <p class="text-2xl font-semibold">{{ statistics.presentToday }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center">
            <div class="rounded-full p-3 bg-yellow-100">
              <i class="fas fa-hourglass-half text-xl text-yellow-600"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Terlambat</p>
              <p class="text-2xl font-semibold">{{ statistics.late }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center">
            <div class="rounded-full p-3 bg-green-100">
              <i class="fas fa-calendar-alt text-xl text-green-600"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Cuti</p>
              <p class="text-2xl font-semibold">{{ statistics.onLeave }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center">
            <div class="rounded-full p-3 bg-purple-100">
              <i class="fas fa-clipboard-list text-xl text-purple-600"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Menunggu Persetujuan</p>
              <p class="text-2xl font-semibold">{{ statistics.pendingLeaveRequests }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pengajuan Cuti Terbaru -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Pengajuan Cuti Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Cuti</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="request in leaveRequests" :key="request.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ request.employee }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ request.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ request.startDate }} - {{ request.endDate }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="{
                    'px-2 py-1 text-xs rounded-full': true,
                    'bg-yellow-100 text-yellow-800': request.status === 'Pending',
                    'bg-green-100 text-green-800': request.status === 'Approved',
                    'bg-red-100 text-red-800': request.status === 'Rejected'
                  }">
                    {{ request.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="leaveRequests.length === 0">
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                  Tidak ada pengajuan cuti terbaru
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pengumuman -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Pengumuman</h3>
          <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-eye mr-2"></i>Lihat Detail
          </button>
        </div>
        <div class="space-y-4">
          <div v-for="announcement in announcements" :key="announcement.id" class="border rounded-lg p-6">
            <div class="flex justify-between items-start mb-2">
              <h4 class="font-semibold">{{ announcement.title }}</h4>
              <span class="text-sm text-gray-500">{{ announcement.date }}</span>
            </div>
            <p class="text-gray-600">{{ announcement.content }}</p>
          </div>
          <div v-if="announcements.length === 0" class="border rounded-lg p-6 text-center text-gray-500">
            Tidak ada pengumuman terbaru
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
