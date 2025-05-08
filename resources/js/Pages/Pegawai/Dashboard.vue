<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue'
import StatistikChart from '@/Components/StatistikChart.vue'

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

const user = usePage().props.auth.user
const sisaCuti = ref([
  { jenis: 'Cuti Tahunan', sisa: 12 },
  { jenis: 'Cuti Sakit', sisa: 12 }
])
const statistik = ref({
  hadir: 20, terlambat: 2, izin: 1, cuti: 3
})
const riwayatAbsensi = ref([
  { id: 1, tanggal: '2024-03-23', check_in: '07:55', check_out: '17:00', status: 'Hadir' },
  { id: 2, tanggal: '2024-03-22', check_in: '08:10', check_out: '17:05', status: 'Terlambat' },
  { id: 3, tanggal: '2024-03-21', check_in: '07:50', check_out: '17:00', status: 'Hadir' }
])

function formatTanggalIndo(dateStr) {
  if (!dateStr) return '-';
  const bulan = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  const date = new Date(dateStr);
  const tgl = date.getDate().toString().padStart(2, '0');
  const bln = bulan[date.getMonth()];
  const thn = date.getFullYear();
  return `${tgl} ${bln} ${thn}`;
}
</script>

<template>
  <Head title="Dashboard Pegawai" />

  <AuthenticatedLayout>
    <template #header>
      <h1 class="text-xl font-semibold text-gray-900">Dashboard Pegawai</h1>
    </template>
    <div class="py-12">
      <div class="max-w-6xl mx-auto mb-6 flex flex-col items-center">
        <div
          class="text-7xl font-extrabold tracking-widest text-gray-900 drop-shadow-lg transition-all duration-300"
          style="letter-spacing: 0.15em;"
        >
          {{ currentTime }}
        </div>
        <div class="text-lg text-gray-600 mt-2">{{ currentDate }}</div>
      </div>
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Profil Singkat -->
        <div class="bg-white p-6 rounded-lg shadow flex flex-col items-center">
          <img src="/images/user.png" class="w-20 h-20 rounded-full mb-2 border-2 border-blue-200" />
          <div class="text-lg font-bold">{{ user.name }}</div>
          <div class="text-gray-600 text-sm">NIP: {{ user.nip }}</div>
          <div class="text-gray-600 text-sm">Role: {{ user.role }}</div>
        </div>
        <!-- Sisa Cuti -->
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="font-semibold mb-2">Sisa Cuti</div>
          <div v-for="cuti in sisaCuti" :key="cuti.jenis" class="mb-2">
            <div class="text-gray-700">{{ cuti.jenis }}</div>
            <div class="text-xl font-bold">{{ cuti.sisa }} hari</div>
          </div>
        </div>
        <!-- Statistik Absensi -->
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="font-semibold mb-2">Statistik Absensi</div>
          <StatistikChart :data="statistik" />
        </div>
      </div>
      <!-- Riwayat Absensi Singkat -->
      <div class="max-w-6xl mx-auto mt-8 bg-white p-6 rounded-lg shadow">
        <div class="font-semibold mb-2">Riwayat Absensi Terakhir</div>
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="text-left py-2 px-2">Tanggal</th>
              <th class="text-left py-2 px-2">Check In</th>
              <th class="text-left py-2 px-2">Check Out</th>
              <th class="text-left py-2 px-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="absen in riwayatAbsensi" :key="absen.id">
              <td class="py-2 px-2">{{ formatTanggalIndo(absen.tanggal) }}</td>
              <td class="py-2 px-2">{{ absen.check_in }}</td>
              <td class="py-2 px-2">{{ absen.check_out }}</td>
              <td class="py-2 px-2">{{ absen.status }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Hapus definisi CSS manual dan gunakan kelas Tailwind langsung di template */
</style>
