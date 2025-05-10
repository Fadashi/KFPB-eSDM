<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-5xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Shift</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Shift -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Data Shift' : 'Tambah Data Shift' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="shift_name" class="block text-sm font-medium text-gray-700">Shift</label>
                <input
                  type="text"
                  id="shift_name"
                  v-model="form.shift"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
                <div v-if="errors.shift" class="text-red-500 text-sm mt-1">{{ errors.shift }}</div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label for="jam_masuk" class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                  <input
                    type="time"
                    id="jam_masuk"
                    v-model="form.jam_masuk"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                  />
                  <div v-if="errors.jam_masuk" class="text-red-500 text-sm mt-1">{{ errors.jam_masuk }}</div>
                </div>
                <div>
                  <label for="jam_keluar" class="block text-sm font-medium text-gray-700">Jam Keluar</label>
                  <input
                    type="time"
                    id="jam_keluar"
                    v-model="form.jam_keluar"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                  />
                  <div v-if="errors.jam_keluar" class="text-red-500 text-sm mt-1">{{ errors.jam_keluar }}</div>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <div>
                <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                <input
                  type="text"
                  id="latitude"
                  v-model="form.latitude"
                  placeholder="Contoh: -6.123456"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <div v-if="errors.latitude" class="text-red-500 text-sm mt-1">{{ errors.latitude }}</div>
              </div>
              <div>
                <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                <input
                  type="text"
                  id="longitude"
                  v-model="form.longitude"
                  placeholder="Contoh: 106.123456"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <div v-if="errors.longitude" class="text-red-500 text-sm mt-1">{{ errors.longitude }}</div>
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

        <!-- Tabel Daftar Shift -->
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
              Tambah Shift
            </button>
          </div>

          <div v-if="loading && !shifts.length" class="text-center py-6">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="mt-2 text-gray-600">Memuat data...</p>
          </div>

          <div v-else class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latitude</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Longitude</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(shift, index) in paginatedShifts" :key="shift.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ startIndex + index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.shift }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.jam_masuk }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.jam_keluar }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.latitude || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.longitude || '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(shift)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="confirmDelete(shift)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredShifts.length === 0 && !loading">
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                      Tidak ada data yang ditemukan
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="filteredShifts.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <div class="text-sm text-gray-500 w-full sm:w-auto text-center sm:text-left">
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredShifts.length }} data
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
          <p class="mt-2 text-gray-600">Apakah Anda yakin ingin menghapus shift <strong>{{ selectedShift?.shift }}</strong>?</p>
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
              @click="deleteShift"
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
const shifts = ref([]);
const loading = ref(false);
const errors = ref({});
const showDeleteDialog = ref(false);
const selectedShift = ref(null);

const form = reactive({
  id: null,
  shift: '',
  jam_masuk: '',
  jam_keluar: '',
  latitude: '',
  longitude: ''
});

// Memuat data shift dari API
const fetchShifts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/shift');
    shifts.value = response.data.shift;
  } catch (error) {
    console.error('Error fetching shifts:', error);
    toast.error('Gagal memuat data shift');
  } finally {
    loading.value = false;
  }
};

// Filter shift berdasarkan pencarian
const filteredShifts = computed(() => {
  if (!search.value) return shifts.value;
  
  const searchLower = search.value.toLowerCase();
  return shifts.value.filter(shift => 
    shift.shift.toLowerCase().includes(searchLower)
  );
});

// Paginasi
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => {
  const end = startIndex.value + itemsPerPage;
  return end > filteredShifts.value.length ? filteredShifts.value.length : end;
});

const paginatedShifts = computed(() => {
  return filteredShifts.value.slice(startIndex.value, endIndex.value);
});

const totalPages = computed(() => {
  return Math.ceil(filteredShifts.value.length / itemsPerPage);
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
  Object.keys(form).forEach(key => {
    form[key] = key === 'id' ? null : '';
  });
  showForm.value = true;
};

// Menampilkan form edit
const showEditForm = (shift) => {
  isEditing.value = true;
  errors.value = {};
  Object.keys(form).forEach(key => {
    form[key] = shift[key];
  });
  form.id = shift.id;
  showForm.value = true;
};

// Tutup form
const closeForm = () => {
  showForm.value = false;
  errors.value = {};
};

// Konfirmasi delete
const confirmDelete = (shift) => {
  selectedShift.value = shift;
  showDeleteDialog.value = true;
};

// Mengirim data ke API
const handleSubmit = async () => {
  loading.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await axios.put(`/api/shift/${form.id}`, form);
      toast.success('Data shift berhasil diperbarui');
    } else {
      await axios.post('/api/shift', form);
      toast.success('Data shift berhasil ditambahkan');
    }
    
    fetchShifts(); // Refresh data
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
const deleteShift = async () => {
  if (!selectedShift.value) return;
  
  loading.value = true;
  try {
    await axios.delete(`/api/shift/${selectedShift.value.id}`);
    toast.success('Data shift berhasil dihapus');
    fetchShifts(); // Refresh data
    showDeleteDialog.value = false;
  } catch (error) {
    console.error('Error deleting shift:', error);
    toast.error('Gagal menghapus data shift');
  } finally {
    loading.value = false;
  }
};

// Muat data saat komponen muncul
watch(() => props.show, (newValue) => {
  if (newValue) {
    fetchShifts();
  }
});

onMounted(() => {
  if (props.show) {
    fetchShifts();
  }
});
</script> 