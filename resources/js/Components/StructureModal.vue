<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-6xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Struktur</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Form Tambah/Edit Struktur -->
        <div v-if="showForm" class="p-6 border-b">
          <h3 class="text-lg font-medium mb-4">{{ isEditing ? 'Edit Data Struktur' : 'Tambah Data Struktur' }}</h3>
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="npp" class="block text-sm font-medium text-gray-700">NPP Pegawai</label>
                <input
                  type="text"
                  id="npp"
                  v-model="form.npp"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Pegawai</label>
                <input
                  type="text"
                  id="nama"
                  v-model="form.nama"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label for="npp_atasan" class="block text-sm font-medium text-gray-700">NPP Atasan</label>
                <input
                  type="text"
                  id="npp_atasan"
                  v-model="form.npp_atasan"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label for="nama_atasan" class="block text-sm font-medium text-gray-700">Nama Atasan</label>
                <input
                  type="text"
                  id="nama_atasan"
                  v-model="form.nama_atasan"
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

        <!-- Tabel Daftar Struktur -->
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
              Tambah Struktur
            </button>
          </div>

          <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NPP Pegawai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pegawai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NPP Atasan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Atasan</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(struktur, index) in paginatedStructures" :key="struktur.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">{{ startIndex + index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ struktur.npp }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ struktur.nama }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ struktur.npp_atasan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ struktur.nama_atasan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(struktur)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="deleteStructure(struktur.id)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredStructures.length === 0">
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
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
              Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredStructures.length }} data
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

const structures = ref([
  {
    id: 1,
    npp: '12345',
    nama: 'John Doe',
    npp_atasan: '11111',
    nama_atasan: 'Jane Smith'
  },
  {
    id: 2,
    npp: '67890',
    nama: 'Alice Johnson',
    npp_atasan: '11111',
    nama_atasan: 'Jane Smith'
  },
  {
    id: 3,
    npp: '11111',
    nama: 'Jane Smith',
    npp_atasan: '00001',
    nama_atasan: 'Robert Wilson'
  }
]);

const form = reactive({
  id: null,
  npp: '',
  nama: '',
  npp_atasan: '',
  nama_atasan: ''
});

// Computed properties for filtering and pagination
const filteredStructures = computed(() => {
  return structures.value.filter(structure => {
    const searchLower = search.value.toLowerCase();
    return (
      structure.npp.toLowerCase().includes(searchLower) ||
      structure.nama.toLowerCase().includes(searchLower) ||
      structure.npp_atasan.toLowerCase().includes(searchLower) ||
      structure.nama_atasan.toLowerCase().includes(searchLower)
    );
  });
});

const totalPages = computed(() => {
  return Math.ceil(filteredStructures.value.length / itemsPerPage);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage;
});

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage, filteredStructures.value.length);
});

const paginatedStructures = computed(() => {
  return filteredStructures.value.slice(startIndex.value, endIndex.value);
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
  form.npp = '';
  form.nama = '';
  form.npp_atasan = '';
  form.nama_atasan = '';
};

const showAddForm = () => {
  resetForm();
  showForm.value = true;
  isEditing.value = false;
};

const showEditForm = (structure) => {
  form.id = structure.id;
  form.npp = structure.npp;
  form.nama = structure.nama;
  form.npp_atasan = structure.npp_atasan;
  form.nama_atasan = structure.nama_atasan;
  showForm.value = true;
  isEditing.value = true;
};

const handleSubmit = () => {
  if (isEditing.value) {
    // Update existing structure
    const index = structures.value.findIndex(s => s.id === form.id);
    if (index !== -1) {
      structures.value[index] = { ...form };
    }
  } else {
    // Add new structure
    const newStructure = {
      id: structures.value.length + 1,
      ...form
    };
    structures.value.push(newStructure);
  }
  resetForm();
  showForm.value = false;
  isEditing.value = false;
};

const deleteStructure = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data struktur ini?')) {
    structures.value = structures.value.filter(s => s.id !== id);
    // Reset to first page if current page is empty
    if (paginatedStructures.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
  }
};

// Watch for search changes to reset pagination
watch(search, () => {
  currentPage.value = 1;
});
</script> 