<template>
  <Head title="Riwayat Pengajuan" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :items="[
        { label: 'Dashboard', href: '/pegawai/dashboard', icon: 'fas fa-home' },
        { label: 'Riwayat' }
      ]" />
      <h1 class="text-xl font-semibold text-gray-900">Riwayat Pengajuan</h1>
    </template>
    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow mb-8">
          <h2 class="text-lg font-semibold mb-4 text-gray-900">Riwayat Pengajuan Cuti</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left py-2 px-2">Tanggal Pengajuan</th>
                  <th class="text-left py-2 px-2">Jenis</th>
                  <th class="text-left py-2 px-2">Tanggal Cuti</th>
                  <th class="text-left py-2 px-2">Hari</th>
                  <th class="text-left py-2 px-2">Alasan</th>
                  <th class="text-left py-2 px-2">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cuti in riwayatCuti" :key="cuti.id" class="border-b border-gray-100">
                  <td class="py-2 px-2">{{ formatTanggalIndo(cuti.created_at) }}</td>
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
                </tr>
                <tr v-if="riwayatCuti.length === 0">
                  <td colspan="6" class="text-center text-gray-400 py-4">Belum ada pengajuan cuti</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-semibold mb-4 text-gray-900">Riwayat Pengajuan Lembur</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left py-2 px-2">Tanggal</th>
                  <th class="text-left py-2 px-2">Jam</th>
                  <th class="text-left py-2 px-2">Alasan</th>
                  <th class="text-left py-2 px-2">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="lembur in riwayatLembur" :key="lembur.id" class="border-b border-gray-100">
                  <td class="py-2 px-2">{{ formatTanggalIndo(lembur.tanggal) }}</td>
                  <td class="py-2 px-2">{{ lembur.mulai }} s.d. {{ lembur.selesai }}</td>
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
                </tr>
                <tr v-if="riwayatLembur.length === 0">
                  <td colspan="4" class="text-center text-gray-400 py-4">Belum ada pengajuan lembur</td>
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
import Breadcrumb from '@/Components/Breadcrumb.vue'

const riwayatCuti = ref([])
const riwayatLembur = ref([])

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

function hitungHari(mulai, selesai) {
  if (!mulai || !selesai) return 0;
  const start = new Date(mulai);
  const end = new Date(selesai);
  return Math.max(0, (end - start) / (1000*60*60*24) + 1);
}

onMounted(async () => {
  const resCuti = await axios.get('/pegawai/api/leave-history')
  riwayatCuti.value = resCuti.data
  const resLembur = await axios.get('/pegawai/api/overtime-history')
  riwayatLembur.value = resLembur.data
})
</script> 