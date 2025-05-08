<template>
  <Head title="Persetujuan Cuti" />
  <AuthenticatedLayout>
    <template #header>
      <h1 class="text-xl font-semibold text-gray-900">Persetujuan Cuti</h1>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
          <div v-if="notif" class="mb-4 p-2 rounded bg-green-100 text-green-700 text-center font-bold">{{ notif }}</div>
          
          <!-- Tabel Pengajuan Cuti -->
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left py-2 px-2">Nama Pegawai</th>
                  <th class="text-left py-2 px-2">Jenis Cuti</th>
                  <th class="text-left py-2 px-2">Tanggal</th>
                  <th class="text-left py-2 px-2">Jumlah Hari</th>
                  <th class="text-left py-2 px-2">Alasan</th>
                  <th class="text-left py-2 px-2">Status</th>
                  <th class="text-left py-2 px-2">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cuti in leaveRequests" :key="cuti.id" class="border-b border-gray-100">
                  <td class="py-2 px-2">{{ cuti.user.name }}</td>
                  <td class="py-2 px-2">{{ cuti.jenis }}</td>
                  <td class="py-2 px-2">{{ formatTanggalIndo(cuti.mulai) }} s.d. {{ formatTanggalIndo(cuti.selesai) }}</td>
                  <td class="py-2 px-2">{{ hitungHari(cuti.mulai, cuti.selesai) }}</td>
                  <td class="py-2 px-2">{{ cuti.alasan }}</td>
                  <td class="py-2 px-2">
                    <span :class="{
                      'bg-yellow-100 text-yellow-800': cuti.status === 'Menunggu',
                      'bg-green-100 text-green-800': cuti.status === 'Disetujui',
                      'bg-red-100 text-red-800': cuti.status === 'Ditolak'
                    }" class="px-3 py-1 rounded-full text-sm font-semibold">
                      {{ cuti.status }}
                    </span>
                  </td>
                  <td class="py-2 px-2">
                    <div v-if="cuti.status === 'Menunggu'" class="flex gap-2">
                      <button @click="approveCuti(cuti.id)" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-check"></i> Setujui
                      </button>
                      <button @click="rejectCuti(cuti.id)" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i> Tolak
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="leaveRequests.length === 0">
                  <td colspan="7" class="text-center text-gray-400 py-4">Tidak ada pengajuan cuti yang menunggu persetujuan</td>
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

const leaveRequests = ref([])
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

// Hitung jumlah hari
function hitungHari(mulai, selesai) {
  if (!mulai || !selesai) return 0;
  const start = new Date(mulai);
  const end = new Date(selesai);
  return Math.max(0, (end - start) / (1000*60*60*24) + 1);
}

// Ambil data pengajuan cuti
async function fetchLeaveRequests() {
  try {
    const response = await axios.get('/atasan/api/leave-requests');
    leaveRequests.value = response.data;
  } catch (error) {
    console.error('Error fetching leave requests:', error);
    notif.value = 'Gagal mengambil data pengajuan cuti';
  }
}

// Setujui cuti
async function approveCuti(id) {
  try {
    await axios.put(`/atasan/api/leave-requests/${id}/approve`);
    notif.value = 'Pengajuan cuti berhasil disetujui';
    fetchLeaveRequests();
  } catch (error) {
    console.error('Error approving leave request:', error);
    notif.value = 'Gagal menyetujui pengajuan cuti';
  }
  setTimeout(() => notif.value = '', 4000);
}

// Tolak cuti
async function rejectCuti(id) {
  try {
    await axios.put(`/atasan/api/leave-requests/${id}/reject`);
    notif.value = 'Pengajuan cuti berhasil ditolak';
    fetchLeaveRequests();
  } catch (error) {
    console.error('Error rejecting leave request:', error);
    notif.value = 'Gagal menolak pengajuan cuti';
  }
  setTimeout(() => notif.value = '', 4000);
}

onMounted(() => {
  fetchLeaveRequests();
});
</script> 