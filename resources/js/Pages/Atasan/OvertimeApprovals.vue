<template>
  <Head title="Persetujuan Lembur" />
  <AuthenticatedLayout>
    <template #header>
      <h1 class="text-xl font-semibold text-gray-900">Persetujuan Lembur</h1>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
          <div v-if="notif" class="mb-4 p-2 rounded bg-green-100 text-green-700 text-center font-bold">{{ notif }}</div>
          
          <!-- Tabel Pengajuan Lembur -->
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left py-2 px-2">Nama Pegawai</th>
                  <th class="text-left py-2 px-2">Tanggal</th>
                  <th class="text-left py-2 px-2">Waktu Mulai</th>
                  <th class="text-left py-2 px-2">Waktu Selesai</th>
                  <th class="text-left py-2 px-2">Durasi</th>
                  <th class="text-left py-2 px-2">Alasan</th>
                  <th class="text-left py-2 px-2">Status</th>
                  <th class="text-left py-2 px-2">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="lembur in overtimeRequests" :key="lembur.id" class="border-b border-gray-100">
                  <td class="py-2 px-2">{{ lembur.user.name }}</td>
                  <td class="py-2 px-2">{{ formatTanggalIndo(lembur.tanggal) }}</td>
                  <td class="py-2 px-2">{{ lembur.mulai }}</td>
                  <td class="py-2 px-2">{{ lembur.selesai }}</td>
                  <td class="py-2 px-2">{{ hitungDurasi(lembur.mulai, lembur.selesai) }}</td>
                  <td class="py-2 px-2">{{ lembur.alasan }}</td>
                  <td class="py-2 px-2">
                    <span :class="{
                      'bg-yellow-100 text-yellow-800': lembur.status === 'Menunggu',
                      'bg-green-100 text-green-800': lembur.status === 'Disetujui',
                      'bg-red-100 text-red-800': lembur.status === 'Ditolak'
                    }" class="px-3 py-1 rounded-full text-sm font-semibold">
                      {{ lembur.status }}
                    </span>
                  </td>
                  <td class="py-2 px-2">
                    <div v-if="lembur.status === 'Menunggu'" class="flex gap-2">
                      <button @click="approveLembur(lembur.id)" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-check"></i> Setujui
                      </button>
                      <button @click="rejectLembur(lembur.id)" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i> Tolak
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="overtimeRequests.length === 0">
                  <td colspan="8" class="text-center text-gray-400 py-4">Tidak ada pengajuan lembur yang menunggu persetujuan</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

const overtimeRequests = ref([])
const notif = ref('')

// Format tanggal Indonesia
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

// Hitung durasi lembur
function hitungDurasi(mulai, selesai) {
  if (!mulai || !selesai) return '-';
  const [jamMulai, menitMulai] = mulai.split(':').map(Number);
  const [jamSelesai, menitSelesai] = selesai.split(':').map(Number);
  let totalMenit = (jamSelesai * 60 + menitSelesai) - (jamMulai * 60 + menitMulai);
  if (totalMenit < 0) totalMenit += 24 * 60; // handle lembur lewat tengah malam
  const jam = Math.floor(totalMenit / 60);
  const menit = totalMenit % 60;
  return `${jam} jam${menit > 0 ? ' ' + menit + ' menit' : ''}`.trim();
}

// Ambil data pengajuan lembur
async function fetchOvertimeRequests() {
  try {
    const response = await axios.get('/atasan/api/overtime-requests');
    overtimeRequests.value = response.data;
  } catch (error) {
    console.error('Error fetching overtime requests:', error);
    notif.value = 'Gagal mengambil data pengajuan lembur';
  }
}

// Setujui lembur
async function approveLembur(id) {
  try {
    await axios.put(`/atasan/api/overtime-requests/${id}/approve`);
    notif.value = 'Pengajuan lembur berhasil disetujui';
    fetchOvertimeRequests();
  } catch (error) {
    console.error('Error approving overtime request:', error);
    notif.value = 'Gagal menyetujui pengajuan lembur';
  }
  setTimeout(() => notif.value = '', 4000);
}

// Tolak lembur
async function rejectLembur(id) {
  try {
    await axios.put(`/atasan/api/overtime-requests/${id}/reject`);
    notif.value = 'Pengajuan lembur berhasil ditolak';
    fetchOvertimeRequests();
  } catch (error) {
    console.error('Error rejecting overtime request:', error);
    notif.value = 'Gagal menolak pengajuan lembur';
  }
  setTimeout(() => notif.value = '', 4000);
}

onMounted(() => {
  fetchOvertimeRequests();
});
</script> 