<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-3xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Berkas</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Berkas -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Berkas' : 'Tambah Berkas' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div class="space-y-4">
              <div>
                <label for="nama_berkas" class="block text-sm font-medium text-gray-700">Nama Berkas</label>
                <input
                  type="text"
                  id="nama_berkas"
                  v-model="form.nama_berkas"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
                <div v-if="errors.nama_berkas" class="text-red-500 text-sm mt-1">{{ errors.nama_berkas }}</div>
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
                <span v-if="loading"><i class="fas fa-spinner fa-spin mr-2"></i></span>
                {{ isEditing ? 'Update' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Tabel Daftar Berkas -->
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
              Tambah Berkas
            </button>
          </div>

          <div v-if="loadingData" class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
          </div>

          <div v-else class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Berkas</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in paginatedItems" :key="item.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ item.id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ item.nama_berkas }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(item)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="deleteItem(item.id)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredItems.length === 0">
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                      Tidak ada data yang ditemukan
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="!loadingData && filteredItems.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <div class="text-sm text-gray-500 w-full sm:w-auto text-center sm:text-left">
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredItems.length }} data
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close']);

const showForm = ref(false);
const isEditing = ref(false);
const search = ref('');
const currentPage = ref(1);
const itemsPerPage = 5;
const items = ref([]);
const loadingData = ref(true);
const loading = ref(false);
const errors = reactive({});

const form = reactive({
  id: null,
  nama_berkas: ''
});

// Computed properties for filtering and pagination
const filteredItems = computed(() => {
  return items.value.filter(item => {
    const searchLower = search.value.toLowerCase();
    return (
      item.nama_berkas?.toLowerCase().includes(searchLower) ||
      item.id.toString().includes(searchLower)
    );
  });
});

const totalPages = computed(() => {
  return Math.ceil(filteredItems.value.length / itemsPerPage);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage;
});

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage, filteredItems.value.length);
});

const paginatedItems = computed(() => {
  return filteredItems.value.slice(startIndex.value, endIndex.value);
});

// Fetch data
const fetchItems = async () => {
  loadingData.value = true;
  try {
    const response = await axios.get('/api/berkas');
    items.value = response.data.berkas;
  } catch (error) {
    console.error('Error fetching berkas data:', error);
  } finally {
    loadingData.value = false;
  }
};

// Methods
const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const closeModal = () => {
  emit('close');
  resetForm();
  showForm.value = false;
  isEditing.value = false;
};

const closeForm = () => {
  resetForm();
  showForm.value = false;
  isEditing.value = false;
};

const resetForm = () => {
  form.id = null;
  form.nama_berkas = '';
  Object.keys(errors).forEach(key => delete errors[key]);
};

const showAddForm = () => {
  resetForm();
  showForm.value = true;
  isEditing.value = false;
};

const showEditForm = (item) => {
  form.id = item.id;
  form.nama_berkas = item.nama_berkas;
  showForm.value = true;
  isEditing.value = true;
};

const handleSubmit = async () => {
  loading.value = true;
  Object.keys(errors).forEach(key => delete errors[key]);
  
  try {
    if (isEditing.value) {
      // Update existing item
      const response = await axios.put(`/api/berkas/${form.id}`, form);
      // Replace the updated item in the items array
      const index = items.value.findIndex(p => p.id === form.id);
      if (index !== -1) {
        items.value[index] = response.data.berkas;
      }
    } else {
      // Add new item
      const response = await axios.post('/api/berkas', form);
      items.value.push(response.data.berkas);
    }
    
    resetForm();
    showForm.value = false;
    isEditing.value = false;
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errors) {
      // Handle validation errors
      Object.assign(errors, error.response.data.errors);
    } else {
      console.error('Error submitting form:', error);
      alert('Terjadi kesalahan saat menyimpan data');
    }
  } finally {
    loading.value = false;
  }
};

const deleteItem = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data berkas ini?')) {
    try {
      await axios.delete(`/api/berkas/${id}`);
      items.value = items.value.filter(item => item.id !== id);
      
      // Reset to first page if current page is empty
      if (paginatedItems.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
    } catch (error) {
      console.error('Error deleting berkas:', error);
      alert('Terjadi kesalahan saat menghapus data');
    }
  }
};

// Watch for search changes to reset pagination
watch(search, () => {
  currentPage.value = 1;
});

// Watch for show prop to fetch data
watch(() => props.show, (newValue) => {
  if (newValue) {
    fetchItems();
  }
});

// Fetch data on mount if modal is shown
onMounted(() => {
  if (props.show) {
    fetchItems();
  }
});
</script> 