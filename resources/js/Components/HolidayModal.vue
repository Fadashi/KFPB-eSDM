<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-4xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Libur</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Libur -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Data Libur' : 'Tambah Data Libur' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="tanggal_libur" class="block text-sm font-medium text-gray-700">Tanggal Libur</label>
                <input
                  type="date"
                  id="tanggal_libur"
                  v-model="form.tanggal_libur"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
                <div v-if="errors.tanggal_libur" class="text-red-500 text-sm mt-1">{{ errors.tanggal_libur }}</div>
              </div>
              <div>
                <label for="nama_libur" class="block text-sm font-medium text-gray-700">Nama Libur</label>
                <input
                  type="text"
                  id="nama_libur"
                  v-model="form.nama_libur"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
                <div v-if="errors.nama_libur" class="text-red-500 text-sm mt-1">{{ errors.nama_libur }}</div>
              </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
              <button
                type="button"
                @click="closeForm"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md"
              >
                Batal
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md"
                :disabled="loading"
              >
                <span v-if="loading">
                  <i class="fas fa-spinner fa-spin mr-2"></i>
                </span>
                {{ isEditing ? 'Update' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Tabel Daftar Libur -->
        <div class="p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div class="w-full sm:w-auto">
              <div class="relative">
                <input
                  type="text"
                  v-model="search"
                  placeholder="Cari..."
                  class="w-full sm:w-64 pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
              </div>
            </div>
            <button
              v-if="!showForm"
              @click="showAddForm"
              class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center justify-center gap-2"
            >
              <i class="fas fa-plus"></i>
              Tambah Libur
            </button>
          </div>

          <div v-if="loading && !holidays.length" class="text-center py-6">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="mt-2 text-gray-600">Memuat data...</p>
          </div>

          <div v-else class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Libur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Libur</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(libur, index) in paginatedHolidays" :key="libur.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ startIndex + index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(libur.tanggal_libur) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ libur.nama_libur }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(libur)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="confirmDelete(libur)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredHolidays.length === 0 && !loading">
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                      Tidak ada data yang ditemukan
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="filteredHolidays.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <div class="text-sm text-gray-500 w-full sm:w-auto text-center sm:text-left">
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredHolidays.length }} data
            </div>
            <div class="flex gap-2">
              <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded border"
                :class="currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'"
              >
                <i class="fas fa-chevron-left"></i>
              </button>
              <div class="flex gap-1">
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="currentPage = page"
                  class="px-3 py-1 rounded border"
                  :class="currentPage === page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'"
                >
                  {{ page }}
                </button>
              </div>
              <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border"
                :class="currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'"
              >
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dialog Konfirmasi Hapus -->
    <div v-if="showDeleteDialog" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg w-full max-w-md">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900">Konfirmasi Hapus</h3>
          <p class="mt-2 text-gray-600">Apakah Anda yakin ingin menghapus libur <strong>{{ selectedHoliday?.nama_libur }}</strong>?</p>
          <div class="mt-4 flex justify-end gap-2">
            <button
              type="button"
              @click="showDeleteDialog = false"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md"
            >
              Batal
            </button>
            <button
              type="button"
              @click="deleteHoliday"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md"
              :disabled="loading"
            >
              <span v-if="loading">
                <i class="fas fa-spinner fa-spin mr-2"></i>
              </span>
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close']);

const toast = useToast();
const showForm = ref(false);
const isEditing = ref(false);
const search = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const holidays = ref([]);
const loading = ref(false);
const errors = ref({});
const showDeleteDialog = ref(false);
const selectedHoliday = ref(null);

const form = reactive({
  id: null,
  tanggal_libur: '',
  nama_libur: ''
});

// Format date to locale string
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Memuat data libur dari API
const fetchHolidays = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/libur');
    holidays.value = response.data.libur;
  } catch (error) {
    console.error('Error fetching holidays:', error);
    toast.error('Gagal memuat data libur');
  } finally {
    loading.value = false;
  }
};

// Filter libur berdasarkan pencarian
const filteredHolidays = computed(() => {
  if (!search.value) return holidays.value;
  
  const searchLower = search.value.toLowerCase();
  return holidays.value.filter(holiday => 
    holiday.nama_libur.toLowerCase().includes(searchLower) ||
    formatDate(holiday.tanggal_libur).toLowerCase().includes(searchLower)
  );
});

// Paginasi
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => {
  const end = startIndex.value + itemsPerPage;
  return end > filteredHolidays.value.length ? filteredHolidays.value.length : end;
});

const paginatedHolidays = computed(() => {
  return filteredHolidays.value.slice(startIndex.value, endIndex.value);
});

const totalPages = computed(() => {
  return Math.ceil(filteredHolidays.value.length / itemsPerPage);
});

// Reset halaman saat pencarian berubah
watch(search, () => {
  currentPage.value = 1;
});

// Navigasi paginasi
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Menampilkan form tambah
const showAddForm = () => {
  isEditing.value = false;
  errors.value = {};
  form.id = null;
  form.tanggal_libur = '';
  form.nama_libur = '';
  showForm.value = true;
};

// Menampilkan form edit
const showEditForm = (holiday) => {
  isEditing.value = true;
  errors.value = {};
  form.id = holiday.id;
  form.tanggal_libur = holiday.tanggal_libur;
  form.nama_libur = holiday.nama_libur;
  showForm.value = true;
};

// Tutup form
const closeForm = () => {
  showForm.value = false;
  errors.value = {};
};

// Konfirmasi delete
const confirmDelete = (holiday) => {
  selectedHoliday.value = holiday;
  showDeleteDialog.value = true;
};

// Mengirim data ke API
const handleSubmit = async () => {
  loading.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await axios.put(`/api/libur/${form.id}`, form);
      toast.success('Data libur berhasil diperbarui');
    } else {
      await axios.post('/api/libur', form);
      toast.success('Data libur berhasil ditambahkan');
    }
    
    fetchHolidays(); // Refresh data
    closeForm();
  } catch (error) {
    console.error('Error submitting form:', error);
    
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors;
    } else {
      toast.error('Terjadi kesalahan saat menyimpan data');
    }
  } finally {
    loading.value = false;
  }
};

// Menghapus data
const deleteHoliday = async () => {
  if (!selectedHoliday.value) return;
  
  loading.value = true;
  try {
    await axios.delete(`/api/libur/${selectedHoliday.value.id}`);
    toast.success('Data libur berhasil dihapus');
    fetchHolidays(); // Refresh data
    showDeleteDialog.value = false;
  } catch (error) {
    console.error('Error deleting holiday:', error);
    toast.error('Gagal menghapus data libur');
  } finally {
    loading.value = false;
  }
};

// Muat data saat komponen muncul
watch(() => props.show, (newValue) => {
  if (newValue) {
    fetchHolidays();
  }
});

onMounted(() => {
  if (props.show) {
    fetchHolidays();
  }
});
</script> 