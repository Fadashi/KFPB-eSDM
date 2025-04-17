<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-4xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Bagian</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Bagian -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Data Bagian' : 'Tambah Data Bagian' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="department_code" class="block text-sm font-medium text-gray-700">Kode Bagian</label>
                <input
                  type="text"
                  id="department_code"
                  v-model="form.code"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label for="department_name" class="block text-sm font-medium text-gray-700">Nama Bagian</label>
                <input
                  type="text"
                  id="department_name"
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

        <!-- Tabel Daftar Bagian -->
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
              Tambah Bagian
            </button>
          </div>

          <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bagian</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(department, index) in paginatedDepartments" :key="department.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ startIndex + index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ department.code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ department.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(department)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="deleteDepartment(department.id)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredDepartments.length === 0">
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
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredDepartments.length }} data
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

const emit = defineEmits(['close', 'update:departments']);

const showForm = ref(false);
const isEditing = ref(false);
const search = ref('');
const currentPage = ref(1);
const itemsPerPage = 5;

const departments = ref([
  { 
    id: 1, 
    code: 'B001', 
    name: 'IT Development'
  },
  { 
    id: 2, 
    code: 'B002', 
    name: 'Human Resources'
  },
  { 
    id: 3, 
    code: 'B003', 
    name: 'Finance'
  },
  { 
    id: 4, 
    code: 'B004', 
    name: 'Marketing'
  }
]);

const form = reactive({
  id: null,
  code: '',
  name: ''
});

// Computed properties for filtering and pagination
const filteredDepartments = computed(() => {
  return departments.value.filter(department => {
    const searchLower = search.value.toLowerCase();
    return (
      department.code.toLowerCase().includes(searchLower) ||
      department.name.toLowerCase().includes(searchLower)
    );
  });
});

const totalPages = computed(() => {
  return Math.ceil(filteredDepartments.value.length / itemsPerPage);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage;
});

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage, filteredDepartments.value.length);
});

const paginatedDepartments = computed(() => {
  return filteredDepartments.value.slice(startIndex.value, endIndex.value);
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

const showEditForm = (department) => {
  form.id = department.id;
  form.code = department.code;
  form.name = department.name;
  showForm.value = true;
  isEditing.value = true;
};

const handleSubmit = () => {
  if (isEditing.value) {
    // Update existing department
    const index = departments.value.findIndex(d => d.id === form.id);
    if (index !== -1) {
      departments.value[index] = { ...form };
    }
  } else {
    // Add new department
    const newDepartment = {
      id: departments.value.length + 1,
      ...form
    };
    departments.value.push(newDepartment);
  }
  resetForm();
  showForm.value = false;
  isEditing.value = false;
};

const deleteDepartment = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus bagian ini?')) {
    departments.value = departments.value.filter(d => d.id !== id);
    // Reset to first page if current page is empty
    if (paginatedDepartments.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
  }
};

// Watch for search changes to reset pagination
watch(search, () => {
  currentPage.value = 1;
});
</script> 