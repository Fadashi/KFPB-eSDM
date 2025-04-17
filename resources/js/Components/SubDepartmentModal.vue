<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-4xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Sub Bagian</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Sub Bagian -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Data Sub Bagian' : 'Tambah Data Sub Bagian' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="subdepartment_code" class="block text-sm font-medium text-gray-700">Kode Sub Bagian</label>
                <input
                  type="text"
                  id="subdepartment_code"
                  v-model="form.code"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label for="subdepartment_name" class="block text-sm font-medium text-gray-700">Nama Sub Bagian</label>
                <input
                  type="text"
                  id="subdepartment_name"
                  v-model="form.name"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
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
              >
                {{ isEditing ? 'Update' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Tabel Daftar Sub Bagian -->
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
              Tambah Sub Bagian
            </button>
          </div>

          <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub Bagian</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(subdepartment, index) in paginatedSubDepartments" :key="subdepartment.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ startIndex + index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ subdepartment.code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ subdepartment.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(subdepartment)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="deleteSubDepartment(subdepartment.id)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredSubDepartments.length === 0">
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                      Tidak ada data yang ditemukan
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pagination -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <div class="text-sm text-gray-500 w-full sm:w-auto text-center sm:text-left">
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredSubDepartments.length }} data
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
import { ref, reactive, computed, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close', 'update:subdepartments']);

const showForm = ref(false);
const isEditing = ref(false);
const search = ref('');
const currentPage = ref(1);
const itemsPerPage = 5;

const subdepartments = ref([
  { 
    id: 1, 
    code: 'SB001', 
    name: 'Frontend Development'
  },
  { 
    id: 2, 
    code: 'SB002', 
    name: 'Backend Development'
  },
  { 
    id: 3, 
    code: 'SB003', 
    name: 'UI/UX Design'
  },
  { 
    id: 4, 
    code: 'SB004', 
    name: 'Quality Assurance'
  }
]);

const form = reactive({
  id: null,
  code: '',
  name: ''
});

// Computed properties for filtering and pagination
const filteredSubDepartments = computed(() => {
  return subdepartments.value.filter(subdepartment => {
    const searchLower = search.value.toLowerCase();
    return (
      subdepartment.code.toLowerCase().includes(searchLower) ||
      subdepartment.name.toLowerCase().includes(searchLower)
    );
  });
});

const totalPages = computed(() => {
  return Math.ceil(filteredSubDepartments.value.length / itemsPerPage);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage;
});

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage, filteredSubDepartments.value.length);
});

const paginatedSubDepartments = computed(() => {
  return filteredSubDepartments.value.slice(startIndex.value, endIndex.value);
});

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
  form.code = '';
  form.name = '';
};

const showAddForm = () => {
  resetForm();
  showForm.value = true;
  isEditing.value = false;
};

const showEditForm = (subdepartment) => {
  form.id = subdepartment.id;
  form.code = subdepartment.code;
  form.name = subdepartment.name;
  showForm.value = true;
  isEditing.value = true;
};

const handleSubmit = () => {
  if (isEditing.value) {
    // Update existing subdepartment
    const index = subdepartments.value.findIndex(d => d.id === form.id);
    if (index !== -1) {
      subdepartments.value[index] = { ...form };
    }
  } else {
    // Add new subdepartment
    const newSubDepartment = {
      id: subdepartments.value.length + 1,
      ...form
    };
    subdepartments.value.push(newSubDepartment);
  }
  resetForm();
  showForm.value = false;
  isEditing.value = false;
};

const deleteSubDepartment = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus sub bagian ini?')) {
    subdepartments.value = subdepartments.value.filter(d => d.id !== id);
    // Reset to first page if current page is empty
    if (paginatedSubDepartments.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
  }
};

// Watch for search changes to reset pagination
watch(search, () => {
  currentPage.value = 1;
});
</script> 