<template>
  <Head title="Pengajuan Lembur Pegawai" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :items="[
        { label: 'Dashboard', href: '/pegawai/dashboard', icon: 'fas fa-home' },
        { label: 'Pengajuan Lembur' }
      ]" />
      <h1 class="text-xl font-semibold text-gray-900">Pengajuan Lembur</h1>
    </template>
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;">
          <h2 class="text-xl font-semibold mb-4 text-gray-900">Pengajuan Lembur</h2>
          <div v-if="notif" class="mb-4 p-2 rounded bg-green-100 text-green-700 text-center font-bold">{{ notif }}</div>
          <form @submit.prevent="openModal" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lembur</label>
              <input type="date" v-model="form.tanggal" class="input w-full" />
              <p v-if="errors.tanggal" class="text-red-500 text-xs mt-1">{{ errors.tanggal }}</p>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
              <input type="time" v-model="form.mulai" class="input w-full" />
              <p v-if="errors.mulai" class="text-red-500 text-xs mt-1">{{ errors.mulai }}</p>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
              <input type="time" v-model="form.selesai" class="input w-full" />
              <p v-if="errors.selesai" class="text-red-500 text-xs mt-1">{{ errors.selesai }}</p>
            </div>
            <div class="mb-4 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Lembur</label>
              <textarea v-model="form.alasan" maxlength="500" class="input w-full"></textarea>
              <p v-if="errors.alasan" class="text-red-500 text-xs mt-1">{{ errors.alasan }}</p>
            </div>
            <div class="mb-4 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran Dokumen</label>
              <input type="file" @change="onFileChange" class="input w-full" />
              <p v-if="errors.lampiran" class="text-red-500 text-xs mt-1">{{ errors.lampiran }}</p>
            </div>
            <div class="md:col-span-2 flex justify-end mt-2">
              <button type="submit" class="btn-primary px-8 py-2 text-base rounded-md shadow font-bold tracking-wide flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                Ajukan Lembur
              </button>
            </div>
          </form>
          <!-- Modal Konfirmasi -->
          <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-xl p-8 w-full max-w-md shadow-2xl border border-gray-200 relative animate-fadeIn">
              <h3 class="text-xl font-bold mb-6 text-gray-900 text-center tracking-wide">Konfirmasi Pengajuan Lembur</h3>
              <ul class="mb-6 text-base text-gray-700 space-y-2">
                <li><span class="font-semibold">Tanggal:</span> <span class="ml-1">{{ formatTanggalIndo(form.tanggal) }}</span></li>
                <li><span class="font-semibold">Jam:</span> <span class="ml-1">{{ form.mulai }} s.d. {{ form.selesai }}</span></li>
                <li><span class="font-semibold">Alasan:</span> <span class="ml-1">{{ form.alasan }}</span></li>
                <li v-if="form.lampiran"><span class="font-semibold">Lampiran:</span> <span class="ml-1">{{ form.lampiran.name }}</span></li>
              </ul>
              <div class="flex gap-3 justify-end mt-4">
                <button @click="showModal=false" class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold shadow hover:bg-gray-300 transition">Batal</button>
                <button @click="submitLembur" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold shadow transition">Kirim</button>
              </div>
              <button @click="showModal=false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
            </div>
          </div>
          <!-- Tabel Riwayat Pengajuan Lembur -->
          <div class="mt-8 bg-white p-6 rounded-lg shadow border border-gray-200">
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
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'
import Breadcrumb from '@/Components/Breadcrumb.vue'

const form = ref({
  tanggal: '',
  mulai: '',
  selesai: '',
  alasan: '',
  lampiran: null,
})
const errors = ref({})
const notif = ref('')
const showModal = ref(false)
const riwayatLembur = ref([])

function openModal() {
  errors.value = {}
  if (!form.value.tanggal) errors.value.tanggal = 'Tanggal wajib diisi.'
  if (!form.value.mulai) errors.value.mulai = 'Jam mulai wajib diisi.'
  if (!form.value.selesai) errors.value.selesai = 'Jam selesai wajib diisi.'
  if (!form.value.alasan) errors.value.alasan = 'Alasan wajib diisi.'
  if (Object.keys(errors.value).length === 0) {
    showModal.value = true
  }
}
async function submitLembur() {
  showModal.value = false
  notif.value = ''
  try {
    const formData = new FormData()
    formData.append('tanggal', form.value.tanggal)
    formData.append('mulai', form.value.mulai)
    formData.append('selesai', form.value.selesai)
    formData.append('alasan', form.value.alasan)
    if (form.value.lampiran) {
      formData.append('lampiran', form.value.lampiran)
    }
    await axios.post('/pegawai/api/overtime-request', formData)
    notif.value = 'Pengajuan lembur berhasil dikirim!'
    const res = await axios.get('/pegawai/api/overtime-history')
    riwayatLembur.value = res.data
  } catch (e) {
    notif.value = 'Gagal mengajukan lembur. Silakan coba lagi.'
  }
  setTimeout(() => notif.value = '', 4000)
}
function onFileChange(e) {
  const file = e.target.files[0]
  if (file && !['image/jpeg','image/png','image/jpg','image/gif','image/webp','application/pdf'].includes(file.type)) {
    notif.value = 'Format file tidak didukung. Hanya gambar (jpg, png, gif, webp) atau PDF.'
    form.value.lampiran = null
    return
  }
  form.value.lampiran = file
}
onMounted(async () => {
  const res = await axios.get('/pegawai/api/overtime-history')
  riwayatLembur.value = res.data
})
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

<style scoped>
.input {
  @apply block w-full rounded-md border-gray-300 shadow-sm py-3 px-4;
}
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-200;
}
.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style> 