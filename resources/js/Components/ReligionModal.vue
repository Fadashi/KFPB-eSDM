<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-2xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Agama</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Agama -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Data Agama' : 'Tambah Data Agama' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div>
              <label for="agama" class="block text-sm font-medium text-gray-700">Agama</label>
              <input
                type="text"
                id="agama"
                v-model="form.agama"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              />
              <div v-if="errors.agama" class="text-red-500 text-sm mt-1">{{ errors.agama }}</div>
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

        <!-- Tabel Daftar Agama -->
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
              Tambah Agama
            </button>
          </div>

          <div v-if="loading && !religions.length" class="text-center py-6">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="mt-2 text-gray-600">Memuat data...</p>
          </div>

          <div v-else class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agama</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(agama, index) in paginatedReligions" :key="agama.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ startIndex + index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ agama.agama }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(agama)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="confirmDelete(agama)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredReligions.length === 0 && !loading">
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                      Tidak ada data yang ditemukan
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="filteredReligions.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <div class="text-sm text-gray-500 w-full sm:w-auto text-center sm:text-left">
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredReligions.length }} data
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
          <p class="mt-2 text-gray-600">Apakah Anda yakin ingin menghapus agama <strong>{{ selectedReligion?.agama }}</strong>?</p>
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
              @click="deleteReligion"
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
const religions = ref([]);
const loading = ref(false);
const errors = ref({});
const showDeleteDialog = ref(false);
const selectedReligion = ref(null);

const form = reactive({
  id: null,
  agama: ''
});

// Memuat data agama dari API
const fetchReligions = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/agama');
    religions.value = response.data.agama;
  } catch (error) {
    console.error('Error fetching religions:', error);
    toast.error('Gagal memuat data agama');
  } finally {
    loading.value = false;
  }
};

// Filter agama berdasarkan pencarian
const filteredReligions = computed(() => {
  if (!search.value) return religions.value;
  
  const searchLower = search.value.toLowerCase();
  return religions.value.filter(religion => 
    religion.agama.toLowerCase().includes(searchLower)
  );
});

// Paginasi
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => {
  const end = startIndex.value + itemsPerPage;
  return end > filteredReligions.value.length ? filteredReligions.value.length : end;
});

const paginatedReligions = computed(() => {
  return filteredReligions.value.slice(startIndex.value, endIndex.value);
});

const totalPages = computed(() => {
  return Math.ceil(filteredReligions.value.length / itemsPerPage);
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
  form.agama = '';
  showForm.value = true;
};

// Menampilkan form edit
const showEditForm = (religion) => {
  isEditing.value = true;
  errors.value = {};
  form.id = religion.id;
  form.agama = religion.agama;
  showForm.value = true;
};

// Tutup form
const closeForm = () => {
  showForm.value = false;
  errors.value = {};
};

// Konfirmasi delete
const confirmDelete = (religion) => {
  selectedReligion.value = religion;
  showDeleteDialog.value = true;
};

// Mengirim data ke API
const handleSubmit = async () => {
  loading.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await axios.put(`/api/agama/${form.id}`, form);
      toast.success('Data agama berhasil diperbarui');
    } else {
      await axios.post('/api/agama', form);
      toast.success('Data agama berhasil ditambahkan');
    }
    
    fetchReligions(); // Refresh data
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
const deleteReligion = async () => {
  if (!selectedReligion.value) return;
  
  loading.value = true;
  try {
    await axios.delete(`/api/agama/${selectedReligion.value.id}`);
    toast.success('Data agama berhasil dihapus');
    fetchReligions(); // Refresh data
    showDeleteDialog.value = false;
  } catch (error) {
    console.error('Error deleting religion:', error);
    toast.error('Gagal menghapus data agama');
  } finally {
    loading.value = false;
  }
};

// Muat data saat komponen muncul
watch(() => props.show, (newValue) => {
  if (newValue) {
    fetchReligions();
  }
});

onMounted(() => {
  if (props.show) {
    fetchReligions();
  }
});
</script> 