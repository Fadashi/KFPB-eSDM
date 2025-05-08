<template>
  <Head title="Pengajuan Cuti Pegawai" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :items="[
        { label: 'Dashboard', href: '/pegawai/dashboard', icon: 'fas fa-home' },
        { label: 'Pengajuan Cuti' }
      ]" />
      <h1 class="text-xl font-semibold text-gray-900">Pengajuan Cuti</h1>
    </template>
    <div class="py-12">
      <!-- Jam dan Tanggal Besar di Atas Form, Tengah -->
      <div class="text-center mb-8">
        <div class="text-5xl md:text-6xl font-extrabold" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; letter-spacing: 0.1em; text-shadow: 0 2px 8px rgba(0,0,0,0.15); color: #222;">{{ currentTime }}</div>
        <div class="text-xl md:text-2xl font-bold mt-2" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; color: #444;">{{ currentDate }}</div>
      </div>
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;">
          <h2 class="text-xl font-semibold mb-4 text-gray-900" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 700;">Pengajuan Cuti</h2>
          <div v-if="notif" class="mb-4 p-2 rounded bg-green-100 text-green-700 text-center font-bold">{{ notif }}</div>
          <form @submit.prevent="openModal" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Hapus Nama Pegawai, hanya tampilkan NIP -->
            <div class="mb-4 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 600;">NIP</label>
              <input type="text" :value="nipPegawai" readonly class="block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm py-3 px-4 font-semibold text-lg tracking-wide" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;" />
            </div>
            <!-- Jenis Cuti -->
            <div class="mb-4 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 600;">Jenis Cuti</label>
              <select v-model="form.jenis" class="block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm py-3 px-4" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;">
                <option value="">Pilih Jenis Cuti</option>
                <option v-for="opt in jenisCutiOptions" :key="opt.value" :value="opt.value">
                  {{ opt.icon }} {{ opt.label }}
                </option>
              </select>
              <p v-if="errors.jenis" class="text-red-500 text-xs mt-1">{{ errors.jenis }}</p>
            </div>
            <!-- Tanggal Mulai -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 600;">Tanggal Mulai</label>
              <input type="date" v-model="form.mulai" class="input w-full" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;" />
              <p v-if="errors.mulai" class="text-red-500 text-xs mt-1">{{ errors.mulai }}</p>
            </div>
            <!-- Tanggal Selesai -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 600;">Tanggal Selesai</label>
              <input type="date" v-model="form.selesai" class="input w-full" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;" />
              <p v-if="errors.selesai" class="text-red-500 text-xs mt-1">{{ errors.selesai }}</p>
            </div>
            <!-- Jumlah Hari -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 600;">Jumlah Hari</label>
              <input type="number" :value="jumlahHari" readonly class="input w-full bg-gray-100" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;" />
            </div>
            <!-- Alasan -->
            <div class="mb-4 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 600;">Alasan Cuti</label>
              <textarea v-model="form.alasan" maxlength="500" class="input w-full" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;"></textarea>
              <p v-if="errors.alasan" class="text-red-500 text-xs mt-1">{{ errors.alasan }}</p>
            </div>
            <!-- Lampiran -->
            <div class="mb-4 md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; font-weight: 600;">Lampiran Dokumen</label>
              <input type="file" @change="onFileChange" class="input w-full" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif;" />
              <p v-if="errors.lampiran" class="text-red-500 text-xs mt-1">{{ errors.lampiran }}</p>
            </div>
            <!-- Tombol Submit -->
            <div class="md:col-span-2 flex justify-end mt-2">
              <button type="submit" class="btn-primary px-8 py-2 text-base rounded-md shadow font-bold tracking-wide flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                Ajukan Cuti
              </button>
            </div>
          </form>
          <!-- Modal Konfirmasi -->
          <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-xl p-8 w-full max-w-md shadow-2xl border border-gray-200 relative animate-fadeIn">
              <h3 class="text-xl font-bold mb-6 text-gray-900 text-center tracking-wide">Konfirmasi Pengajuan Cuti</h3>
              <ul class="mb-6 text-base text-gray-700 space-y-2">
                <li><span class="font-semibold">Nama:</span> <span class="ml-1">{{ form.nip }}</span></li>
                <li><span class="font-semibold">NIP:</span> <span class="ml-1">{{ form.nip }}</span></li>
                <li><span class="font-semibold">Jenis Cuti:</span> <span class="ml-1">{{ jenisCutiOptions.find(j=>j.value===form.jenis)?.label }}</span></li>
                <li><span class="font-semibold">Tanggal:</span> <span class="ml-1">{{ form.mulai }} s/d {{ form.selesai }}</span></li>
                <li><span class="font-semibold">Jumlah Hari:</span> <span class="ml-1">{{ jumlahHari }}</span></li>
                <li><span class="font-semibold">Alasan:</span> <span class="ml-1">{{ form.alasan }}</span></li>
                <li v-if="form.lampiran"><span class="font-semibold">Lampiran:</span> <span class="ml-1">{{ form.lampiran.name }}</span></li>
              </ul>
              <div class="flex gap-3 justify-end mt-4">
                <button @click="showModal=false" class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold shadow hover:bg-gray-300 transition">Batal</button>
                <button @click="submitCuti" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold shadow transition">Kirim</button>
              </div>
              <button @click="showModal=false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
            </div>
          </div>
          <!-- Tabel Riwayat Pengajuan Cuti -->
          <div class="mt-8 bg-white p-6 rounded-lg shadow border border-gray-200">
            <h2 class="text-lg font-semibold mb-4 text-gray-900">Riwayat Pengajuan Cuti</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full">
                <thead>
                  <tr class="border-b border-gray-200">
                    <th class="text-left py-2 px-2">Tanggal Pengajuan</th>
                    <th class="text-left py-2 px-2">Jenis</th>
                    <th class="text-left py-2 px-2">Tanggal Cuti</th>
                    <th class="text-left py-2 px-2">Jumlah Hari</th>
                    <th class="text-left py-2 px-2">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="cuti in riwayatCuti" :key="cuti.id" class="border-b border-gray-100">
                    <td class="py-2 px-2">{{ formatTanggalIndo(cuti.created_at) }}</td>
                    <td class="py-2 px-2">{{ jenisCutiOptions.find(j=>j.value===cuti.jenis)?.label || cuti.jenis }}</td>
                    <td class="py-2 px-2">{{ formatTanggalIndo(cuti.mulai) }} s.d. {{ formatTanggalIndo(cuti.selesai) }}</td>
                    <td class="py-2 px-2">{{ hitungHari(cuti.mulai, cuti.selesai) }}</td>
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
                    <td colspan="5" class="text-center text-gray-400 py-4">Belum ada pengajuan cuti</td>
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
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'
import Breadcrumb from '@/Components/Breadcrumb.vue'

const props = defineProps(['auth'])
// NIP otomatis dari name user (bukan nip)
const nipPegawai = computed(() => props.auth.user.name)

// Jam real-time dan tanggal
const currentTime = ref('')
const currentDate = ref('')
function updateDateTime() {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
  })
  currentDate.value = now.toLocaleDateString('id-ID', {
    year: 'numeric', month: 'long', day: 'numeric'
  })
}
onMounted(() => {
  updateDateTime()
  setInterval(updateDateTime, 1000)
  // ...existing onMounted code...
})

const form = ref({
  nama: props.auth.user.name,
  nip: nipPegawai.value,
  jenis: '',
  mulai: '',
  selesai: '',
  alasan: '',
  lampiran: null,
})
const jenisCutiOptions = [
  { value: 'tahunan', label: 'Cuti Tahunan'},
  { value: 'sakit', label: 'Cuti Sakit'},
  { value: 'hamil', label: 'Cuti Hamil'},
  { value: 'besar', label: 'Cuti Besar'},
  { value: 'penting', label: 'Cuti Penting'},
]
const jumlahHari = computed(() => {
  if (!form.value.mulai || !form.value.selesai) return 0
  const start = new Date(form.value.mulai)
  const end = new Date(form.value.selesai)
  return Math.max(0, (end - start) / (1000*60*60*24) + 1)
})
const errors = ref({})
const notif = ref('')
const showModal = ref(false)
const riwayatCuti = ref([])

function openModal() {
  errors.value = {}
  // Validasi
  if (!form.value.jenis) errors.value.jenis = 'Jenis cuti wajib dipilih.'
  if (!form.value.mulai) errors.value.mulai = 'Tanggal mulai wajib diisi.'
  if (!form.value.selesai) errors.value.selesai = 'Tanggal selesai wajib diisi.'
  if (form.value.mulai && form.value.selesai && form.value.selesai < form.value.mulai) errors.value.selesai = 'Tanggal selesai tidak boleh lebih awal dari mulai.'
  if (!form.value.alasan) errors.value.alasan = 'Alasan cuti wajib diisi.'
  if (form.value.alasan && form.value.alasan.length > 500) errors.value.alasan = 'Maksimal 500 karakter.'
  if ((form.value.jenis === 'sakit' || form.value.jenis === 'hamil') && !form.value.lampiran) errors.value.lampiran = 'Lampiran wajib untuk cuti sakit/hamil.'
  if (Object.keys(errors.value).length === 0) {
    showModal.value = true
  }
}
async function submitCuti() {
  showModal.value = false
  notif.value = ''
  try {
    const formData = new FormData()
    formData.append('nip', form.value.nip)
    formData.append('jenis', form.value.jenis)
    formData.append('mulai', form.value.mulai)
    formData.append('selesai', form.value.selesai)
    formData.append('alasan', form.value.alasan)
    if (form.value.lampiran) {
      formData.append('lampiran', form.value.lampiran)
    }

    // Kirim ke backend
    await axios.post('/pegawai/api/leave-request', formData)

    notif.value = 'Pengajuan cuti berhasil dikirim!'
    // Ambil ulang data riwayat cuti dari backend
    const res = await axios.get('/pegawai/api/leave-history')
    riwayatCuti.value = res.data
  } catch (e) {
    notif.value = 'Gagal mengajukan cuti. Silakan coba lagi.'
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

// Jika ingin fetch dari API, gunakan onMounted dan axios
onMounted(async () => {
  const res = await axios.get('/pegawai/api/leave-history')
  riwayatCuti.value = res.data
})

// Untuk jumlah hari, jika field tidak ada di backend, bisa dihitung manual:
function hitungHari(mulai, selesai) {
  if (!mulai || !selesai) return 0
  const start = new Date(mulai)
  const end = new Date(selesai)
  return Math.max(0, (end - start) / (1000*60*60*24) + 1)
}

// Fungsi format tanggal Indonesia
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
