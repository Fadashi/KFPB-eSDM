<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

// Data untuk form pengumuman
const formData = ref({
  judul: '',
  isi: '',
  tanggal_mulai: '',
  tanggal_berakhir: '',
});

// State untuk loading
const isSubmitting = ref(false);
// State untuk error
const errors = ref({});

// State untuk mengontrol modal
const isModalOpen = ref(false);

// Data pengumuman
const announcements = ref([
  {
    id: 1,
    judul: 'Pengumuman Test',
    isi: 'Ini adalah isi pengumuman test',
    tanggal_mulai: '2024-03-20',
    tanggal_berakhir: '2024-03-27',
  },
]);

// Counter untuk id baru
let nextId = 2;

const openModal = () => {
  isModalOpen.value = true;
  errors.value = {};
};

const closeModal = () => {
  isModalOpen.value = false;
  errors.value = {};
  // Reset form data
  formData.value = {
    judul: '',
    isi: '',
    tanggal_mulai: '',
    tanggal_berakhir: '',
  };
};

// Method untuk mendapatkan status berdasarkan tanggal
const getStatus = (tanggal_berakhir) => {
  const today = new Date();
  const endDate = new Date(tanggal_berakhir);
  today.setHours(0, 0, 0, 0);
  endDate.setHours(0, 0, 0, 0);
  
  return endDate >= today ? 'aktif' : 'tidak aktif';
};

// Method untuk mendapatkan status badge class
const getStatusBadgeClass = (tanggal_berakhir) => {
  const status = getStatus(tanggal_berakhir);
  switch (status) {
    case 'aktif':
      return 'bg-green-100 text-green-800';
    case 'tidak aktif':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Method untuk validasi form
const validateForm = () => {
  const newErrors = {};
  
  if (!formData.value.judul.trim()) {
    newErrors.judul = 'Judul pengumuman harus diisi';
  }
  
  if (!formData.value.isi.trim()) {
    newErrors.isi = 'Isi pengumuman harus diisi';
  }
  
  if (!formData.value.tanggal_mulai) {
    newErrors.tanggal_mulai = 'Tanggal mulai harus diisi';
  }
  
  if (!formData.value.tanggal_berakhir) {
    newErrors.tanggal_berakhir = 'Tanggal berakhir harus diisi';
  } else if (formData.value.tanggal_mulai && formData.value.tanggal_berakhir) {
    const startDate = new Date(formData.value.tanggal_mulai);
    const endDate = new Date(formData.value.tanggal_berakhir);
    if (endDate < startDate) {
      newErrors.tanggal_berakhir = 'Tanggal berakhir tidak boleh kurang dari tanggal mulai';
    }
  }
  
  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

// Method untuk handle submit form
const handleSubmit = () => {
  if (isSubmitting.value) return;
  
  if (!validateForm()) {
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    // Simulasi delay network request
    setTimeout(() => {
      // Tambah pengumuman baru ke array
      const newAnnouncement = {
        id: nextId++,
        ...formData.value
      };
      
      announcements.value.unshift(newAnnouncement); // Tambah di awal array
      
      // Tampilkan notifikasi sukses
      alert('Pengumuman berhasil ditambahkan!');
      
      // Tutup modal dan reset form
      closeModal();
    }, 500); // Delay 500ms untuk simulasi loading
  } catch (error) {
    errors.value = {
      submit: 'Terjadi kesalahan saat menyimpan pengumuman'
    };
  } finally {
    isSubmitting.value = false;
  }
};

// Method untuk menghapus pengumuman
const deleteAnnouncement = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) {
    try {
      // Simulasi delay network request
      setTimeout(() => {
        // Hapus pengumuman dari array
        announcements.value = announcements.value.filter(item => item.id !== id);
        
        // Tampilkan notifikasi sukses
        alert('Pengumuman berhasil dihapus!');
      }, 300);
    } catch (error) {
      alert('Terjadi kesalahan saat menghapus pengumuman');
    }
  }
};

// Method untuk edit pengumuman
const editAnnouncement = (announcement) => {
  // Set form data dengan data pengumuman yang akan diedit
  formData.value = {
    judul: announcement.judul,
    isi: announcement.isi,
    tanggal_mulai: announcement.tanggal_mulai,
    tanggal_berakhir: announcement.tanggal_berakhir,
  };
  
  // Simpan ID yang sedang diedit
  formData.value.id = announcement.id;
  
  // Buka modal
  openModal();
};

// Method untuk truncate text
const truncateText = (text, length = 50) => {
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};

// Method untuk memformat tanggal
const formatDate = (dateString) => {
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
  <Head title="Admin Announcement"/>

  <AuthenticatedLayout>
    <template #header>
      <!-- Breadcrumbs -->
      <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex">
          <li class="flex items-center">
            <i class="fas fa-home text-blue-600 mr-1"></i>
            <a href="/admin/dashboard" class="text-blue-600 hover:text-blue-800 font-semibold">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
          </li>
          <li class="flex items-center text-gray-700 font-semibold">
            Pengumuman
          </li>
        </ol>
      </nav>
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Pengumuman</h1>
        <button
          @click="openModal"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          <i class="fas fa-plus mr-2"></i> Tambah Pengumuman
        </button>
      </div>
    </template>

    <!-- Modal Form Tambah Pengumuman -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Tambah Pengumuman Baru
                </h3>
                <div class="mt-4">
                  <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                      <label for="judul" class="block text-sm font-medium text-gray-700">Judul Pengumuman</label>
                      <input
                        type="text"
                        id="judul"
                        v-model="formData.judul"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.judul }"
                        required
                        placeholder="Masukkan judul pengumuman"
                      >
                      <p v-if="errors.judul" class="mt-1 text-sm text-red-600">{{ errors.judul }}</p>
                    </div>
                    <div>
                      <label for="isi" class="block text-sm font-medium text-gray-700">Isi Pengumuman</label>
                      <textarea
                        id="isi"
                        v-model="formData.isi"
                        rows="6"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.isi }"
                        required
                        placeholder="Masukkan isi pengumuman"
                      ></textarea>
                      <p v-if="errors.isi" class="mt-1 text-sm text-red-600">{{ errors.isi }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input
                          type="date"
                          id="tanggal_mulai"
                          v-model="formData.tanggal_mulai"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          :class="{ 'border-red-500': errors.tanggal_mulai }"
                          required
                        >
                        <p v-if="errors.tanggal_mulai" class="mt-1 text-sm text-red-600">{{ errors.tanggal_mulai }}</p>
                      </div>
                      <div>
                        <label for="tanggal_berakhir" class="block text-sm font-medium text-gray-700">Tanggal Berakhir</label>
                        <input
                          type="date"
                          id="tanggal_berakhir"
                          v-model="formData.tanggal_berakhir"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          :class="{ 'border-red-500': errors.tanggal_berakhir }"
                          required
                        >
                        <p v-if="errors.tanggal_berakhir" class="mt-1 text-sm text-red-600">{{ errors.tanggal_berakhir }}</p>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              @click="handleSubmit"
              :disabled="isSubmitting"
            >
              <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              @click="closeModal"
              :disabled="isSubmitting"
            >
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Daftar Pengumuman -->
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">No</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Isi</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Tanggal Mulai</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Tanggal Berakhir</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Aksi</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(announcement, index) in announcements" :key="announcement.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ truncateText(announcement.judul, 30) }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ truncateText(announcement.isi, 50) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(announcement.tanggal_mulai) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(announcement.tanggal_berakhir) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusBadgeClass(announcement.tanggal_berakhir)]">
                      {{ getStatus(announcement.tanggal_berakhir) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                      <button
                        @click="editAnnouncement(announcement)"
                        class="text-blue-600 hover:text-blue-800 transition-colors duration-200"
                        title="Edit Pengumuman"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="deleteAnnouncement(announcement.id)"
                        class="text-red-600 hover:text-red-800 transition-colors duration-200"
                        title="Hapus Pengumuman"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <!-- Tampilkan pesan jika tidak ada data -->
                <tr v-if="announcements.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                    Tidak ada pengumuman yang tersedia
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.table-fixed {
  table-layout: fixed;
}
</style>
