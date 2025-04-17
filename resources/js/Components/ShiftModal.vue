<template>
  <div v-show="show" class="fixed inset-0 overflow-y-auto bg-black bg-opacity-50 flex items-start justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-5xl my-8">
      <div class="flex justify-between items-center p-6 border-b">
        <h2 class="text-xl font-semibold">Data Shift</h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
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
                </div>
                <div>
                  <label for="jam_pulang" class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                  <input
                    type="time"
                    id="jam_pulang"
                    v-model="form.jam_pulang"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                  />
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
                  required
                />
              </div>
              <div>
                <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                <input
                  type="text"
                  id="longitude"
                  v-model="form.longitude"
                  placeholder="Contoh: 106.123456"
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

          <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
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
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.jam_pulang }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.latitude }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ shift.longitude }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <button
                        @click="showEditForm(shift)"
                        class="text-blue-600 hover:text-blue-800 mr-2"
                        title="Edit"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        @click="deleteShift(shift.id)"
                        class="text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredShifts.length === 0">
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
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

const emit = defineEmits(['close', 'update:shifts']);

const showForm = ref(false);
const isEditing = ref(false);
const search = ref('');
const currentPage = ref(1);
const itemsPerPage = 5;

const shifts = ref([
  { 
    id: 1, 
    shift: 'Pagi',
    jam_masuk: '07:00',
    jam_pulang: '15:00',
    latitude: '-6.123456',
    longitude: '106.123456'
  },
  { 
    id: 2, 
    shift: 'Siang',
    jam_masuk: '15:00',
    jam_pulang: '23:00',
    latitude: '-6.234567',
    longitude: '106.234567'
  },
  { 
    id: 3, 
    shift: 'Malam',
    jam_masuk: '23:00',
    jam_pulang: '07:00',
    latitude: '-6.345678',
    longitude: '106.345678'
  }
]);

const form = reactive({
  id: null,
  shift: '',
  jam_masuk: '',
  jam_pulang: '',
  latitude: '',
  longitude: ''
});

// Computed properties for filtering and pagination
const filteredShifts = computed(() => {
  return shifts.value.filter(shift => {
    const searchLower = search.value.toLowerCase();
    return (
      shift.shift.toLowerCase().includes(searchLower) ||
      shift.jam_masuk.includes(searchLower) ||
      shift.jam_pulang.includes(searchLower) ||
      shift.latitude.includes(searchLower) ||
      shift.longitude.includes(searchLower)
    );
  });
});

const totalPages = computed(() => {
  return Math.ceil(filteredShifts.value.length / itemsPerPage);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage;
});

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage, filteredShifts.value.length);
});

const paginatedShifts = computed(() => {
  return filteredShifts.value.slice(startIndex.value, endIndex.value);
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
  form.shift = '';
  form.jam_masuk = '';
  form.jam_pulang = '';
  form.latitude = '';
  form.longitude = '';
};

const showAddForm = () => {
  resetForm();
  showForm.value = true;
  isEditing.value = false;
};

const showEditForm = (shift) => {
  form.id = shift.id;
  form.shift = shift.shift;
  form.jam_masuk = shift.jam_masuk;
  form.jam_pulang = shift.jam_pulang;
  form.latitude = shift.latitude;
  form.longitude = shift.longitude;
  showForm.value = true;
  isEditing.value = true;
};

const handleSubmit = () => {
  if (isEditing.value) {
    // Update existing shift
    const index = shifts.value.findIndex(s => s.id === form.id);
    if (index !== -1) {
      shifts.value[index] = { ...form };
    }
  } else {
    // Add new shift
    const newShift = {
      id: shifts.value.length + 1,
      ...form
    };
    shifts.value.push(newShift);
  }
  resetForm();
  showForm.value = false;
  isEditing.value = false;
};

const deleteShift = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus shift ini?')) {
    shifts.value = shifts.value.filter(s => s.id !== id);
    // Reset to first page if current page is empty
    if (paginatedShifts.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
  }
};

// Watch for search changes to reset pagination
watch(search, () => {
  currentPage.value = 1;
});
</script> 