<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

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
  scales: {
    y: {
      beginAtZero: true,
      max: 100,
    },
  },
}

const employees = ref([
  { id: 1, name: 'Budi Santoso', position: 'Frontend Developer', status: 'Hadir', time: '08:00' },
  { id: 2, name: 'Ani Wijaya', position: 'UI/UX Designer', status: 'Terlambat', time: '08:45' },
  { id: 3, name: 'Dedi Kurniawan', position: 'Backend Developer', status: 'Hadir', time: '07:55' },
  { id: 4, name: 'Siti Rahayu', position: 'Project Manager', status: 'Hadir', time: '07:50' },
  { id: 5, name: 'Rudi Hermawan', position: 'QA Engineer', status: 'Izin', time: '-' },
])

const statistics = ref({
  totalEmployees: 50,
  presentToday: 45,
  onLeave: 3,
  late: 2,
})
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
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

            <!-- Grafik Absensi -->
            <div class="chart-container">
                <h2>Grafik Kehadiran Minggu Ini</h2>
                <div class="chart">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Tabel Karyawan -->
            <div class="employee-table">
                <h2>Daftar Kehadiran Hari Ini</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Posisi</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in employees" :key="employee.id">
                            <td>{{ employee.name }}</td>
                            <td>{{ employee.position }}</td>
                            <td>
                                <span class="status" :class="employee.status.toLowerCase()">
                                    {{ employee.status }}
                                </span>
                            </td>
                            <td>{{ employee.time }}</td>
                            <td>
                                <button class="action-btn">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard {
  padding: 20px;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-card i {
  font-size: 2.5em;
  color: #1a237e;
}

.stat-info h3 {
  margin: 0;
  font-size: 0.9em;
  color: #666;
}

.stat-info p {
  margin: 5px 0 0;
  font-size: 1.8em;
  font-weight: bold;
  color: #1a237e;
}

.chart-container {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.chart {
  height: 300px;
}

.employee-table {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

th,
td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background-color: #f8f9fa;
  color: #333;
}

.status {
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 0.9em;
}

.status.hadir {
  background: #e8f5e9;
  color: #4caf50;
}

.status.terlambat {
  background: #fff3e0;
  color: #ff9800;
}

.status.izin {
  background: #f5f5f5;
  color: #9e9e9e;
}

.action-btn {
  background: none;
  border: none;
  color: #1a237e;
  cursor: pointer;
  padding: 5px;
}

.action-btn:hover {
  color: #303f9f;
}

h2 {
  color: #333;
  margin-bottom: 20px;
}
</style>
