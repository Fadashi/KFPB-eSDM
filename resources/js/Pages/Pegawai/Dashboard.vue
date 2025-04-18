<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue'

const currentTime = ref('')
const currentDate = ref('')

const updateDateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
  currentDate.value = now.toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

let timer

onMounted(() => {
  updateDateTime()
  timer = setInterval(updateDateTime, 1000)
})

onUnmounted(() => {
  clearInterval(timer)
})

const attendance = ref({
  status: 'Belum Absen',
  lastCheckIn: null,
  lastCheckOut: null
})

const leaveBalance = ref({
  annual: 12,
  sick: 12,
  used: 3
})

const recentAttendance = ref([
  { date: '2024-03-23', checkIn: '07:55', checkOut: '17:00', status: 'Hadir' },
  { date: '2024-03-22', checkIn: '08:10', checkOut: '17:05', status: 'Terlambat' },
  { date: '2024-03-21', checkIn: '07:50', checkOut: '17:00', status: 'Hadir' },
])

const checkIn = () => {
  attendance.value.status = 'Sudah Absen'
  attendance.value.lastCheckIn = new Date().toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
}

const checkOut = () => {
  attendance.value.lastCheckOut = new Date().toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
}
</script>

<template>
  <Head title="Dashboard Pegawai" />

  <AuthenticatedLayout>
    <template #header>
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <i class="fas fa-home text-gray-600 mr-1"></i>
                        <p class="text-gray-600 font-semibold">Dashboard</p>
                    </li>
                </ol>
            </nav>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Dashboard Saya</h1>
            </div>
        </template>

    <div class="p-6 space-y-6">
      <!-- Absensi Hari Ini -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-semibold mb-4">Absensi Hari Ini</h2>
          <div class="text-center py-6">
            <div class="text-6xl font-bold mb-4 text-gray-800">
              {{ currentTime }}
            </div>
            <div class="text-gray-600 mb-6">
              {{ currentDate }}
            </div>
            <div class="space-x-4">
              <button
                @click="checkIn"
                :disabled="attendance.status === 'Sudah Absen'"
                class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50"
              >
                Check In
              </button>
              <button
                @click="checkOut"
                :disabled="!attendance.lastCheckIn"
                class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50"
              >
                Check Out
              </button>
            </div>
            <div class="mt-4 text-sm text-gray-600">
              <div v-if="attendance.lastCheckIn">Check In: {{ attendance.lastCheckIn }}</div>
              <div v-if="attendance.lastCheckOut">Check Out: {{ attendance.lastCheckOut }}</div>
            </div>
          </div>
        </div>

        <!-- Sisa Cuti -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-semibold mb-4">Sisa Cuti</h2>
          <div class="space-y-4">
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
              <div>
                <h3 class="font-medium">Cuti Tahunan</h3>
                <p class="text-sm text-gray-600">Sisa {{ leaveBalance.annual }} hari</p>
              </div>
              <div class="w-20 h-20">
                <!-- Progress Circle bisa ditambahkan di sini -->
              </div>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
              <div>
                <h3 class="font-medium">Cuti Sakit</h3>
                <p class="text-sm text-gray-600">Sisa {{ leaveBalance.sick }} hari</p>
              </div>
              <div class="w-20 h-20">
                <!-- Progress Circle bisa ditambahkan di sini -->
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Absensi -->
      <div class="bg-white p-6 rounded-lg shadow mt-6">
        <h2 class="text-lg font-semibold mb-4">Riwayat Absensi</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b">
                <th class="text-left py-2">Tanggal</th>
                <th class="text-left py-2">Check In</th>
                <th class="text-left py-2">Check Out</th>
                <th class="text-left py-2">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="record in recentAttendance" :key="record.date" class="border-b">
                <td class="py-2">{{ record.date }}</td>
                <td class="py-2">{{ record.checkIn }}</td>
                <td class="py-2">{{ record.checkOut }}</td>
                <td class="py-2">
                  <span class="px-2 py-1 rounded text-sm" :class="{
                    'bg-green-100 text-green-800': record.status === 'Hadir',
                    'bg-yellow-100 text-yellow-800': record.status === 'Terlambat'
                  }">
                    {{ record.status }}
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

<style scoped>
/* Hapus definisi CSS manual dan gunakan kelas Tailwind langsung di template */
</style>
