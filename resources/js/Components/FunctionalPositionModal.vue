<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-3xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Jabatan Fungsional</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Jabatan Fungsional -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Jabatan Fungsional' : 'Tambah Jabatan Fungsional' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div class="space-y-4">
              <div>
                <label for="jabatan_fungsional" class="block text-sm font-medium text-gray-700">Jabatan Fungsional</label>
                <input
                  type="text"
                  id="jabatan_fungsional"
                  v-model="form.jabatan_fungsional"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                <input
                  type="text"
                  id="kelas"
                  v-model="form.kelas"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label for="umur_pensiun" class="block text-sm font-medium text-gray-700">Umur Pensiun</label>
                <input
                  type="number"
                  id="umur_pensiun"
                  v-model="form.umur_pensiun"
                  min="0"
                  max="100"
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

        <!-- Tabel Daftar Jabatan Fungsional -->
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
              Tambah Jabatan
            </button>
          </div>

          <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan Fungsional</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur Pensiun</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="position in paginatedPositions" :key="position.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ position.id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ position.jabatan_fungsional }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ position.kelas }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ position.umur_pensiun }} Tahun</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(position)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="deletePosition(position.id)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredPositions.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
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
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredPositions.length }} data
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

const emit = defineEmits(['close']);

const showForm = ref(false);
const isEditing = ref(false);
const search = ref('');
const currentPage = ref(1);
const itemsPerPage = 5;

const positions = ref([
  {
    id: 1,
    jabatan_fungsional: 'Peneliti Utama',
    kelas: 'Kelas 1',
    umur_pensiun: 65
  },
  {
    id: 2,
    jabatan_fungsional: 'Peneliti Madya',
    kelas: 'Kelas 2',
    umur_pensiun: 60
  },
  {
    id: 3,
    jabatan_fungsional: 'Peneliti Muda',
    kelas: 'Kelas 3',
    umur_pensiun: 58
  },
  {
    id: 4,
    jabatan_fungsional: 'Peneliti Pertama',
    kelas: 'Kelas 4',
    umur_pensiun: 58
  }
]);

const form = reactive({
  id: null,
  jabatan_fungsional: '',
  kelas: '',
  umur_pensiun: ''
});

// Computed properties for filtering and pagination
const filteredPositions = computed(() => {
  return positions.value.filter(position => {
    const searchLower = search.value.toLowerCase();
    return (
      position.jabatan_fungsional.toLowerCase().includes(searchLower) ||
      position.kelas.toLowerCase().includes(searchLower) ||
      position.umur_pensiun.toString().includes(searchLower) ||
      position.id.toString().includes(searchLower)
    );
  });
});

const totalPages = computed(() => {
  return Math.ceil(filteredPositions.value.length / itemsPerPage);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage;
});

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage, filteredPositions.value.length);
});

const paginatedPositions = computed(() => {
  return filteredPositions.value.slice(startIndex.value, endIndex.value);
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
  form.jabatan_fungsional = '';
  form.kelas = '';
  form.umur_pensiun = '';
};

const showAddForm = () => {
  resetForm();
  showForm.value = true;
  isEditing.value = false;
};

const showEditForm = (position) => {
  form.id = position.id;
  form.jabatan_fungsional = position.jabatan_fungsional;
  form.kelas = position.kelas;
  form.umur_pensiun = position.umur_pensiun;
  showForm.value = true;
  isEditing.value = true;
};

const handleSubmit = () => {
  if (isEditing.value) {
    // Update existing position
    const index = positions.value.findIndex(p => p.id === form.id);
    if (index !== -1) {
      positions.value[index] = { ...form };
    }
  } else {
    // Add new position
    const newPosition = {
      id: positions.value.length + 1,
      ...form
    };
    positions.value.push(newPosition);
  }
  resetForm();
  showForm.value = false;
  isEditing.value = false;
};

const deletePosition = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus jabatan fungsional ini?')) {
    positions.value = positions.value.filter(p => p.id !== id);
    // Reset to first page if current page is empty
    if (paginatedPositions.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
  }
};

// Watch for search changes to reset pagination
watch(search, () => {
  currentPage.value = 1;
});
</script> 