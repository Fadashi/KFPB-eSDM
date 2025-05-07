<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  users: Object,
  filters: Object,
});

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

// State untuk modal create dan edit user
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedUser = ref(null);

// State untuk visibility password
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const showEditPassword = ref(false);

// State untuk pagination
const currentPage = ref(props.users?.current_page || 1);
const perPage = ref(props.filters?.per_page || 10);
const perPageOptions = [5, 10, 20, 50];

// State untuk pencarian
const searchQuery = ref(props.filters?.search || '');
const filterRole = ref(props.filters?.role || 'all');

// Form untuk create user baru
const createForm = useForm({
  name: '',
  username: '',
  password: '',
  password_confirmation: '',
  role: 'pegawai',
});

// Form untuk edit user
const editForm = useForm({
  id: '',
  name: '',
  username: '',
  password: '',
  role: '',
  status: '',
});

// Method untuk mencari user
const searchUsers = () => {
  currentPage.value = 1;
  applyFilters();
};

// Method untuk mengubah filter role
const changeFilterRole = () => {
  currentPage.value = 1;
  applyFilters();
};

// Method untuk menerapkan filters dan pagination
const applyFilters = () => {
  editForm.get(route('admin.users.index'), {
    preserveState: true,
    preserveScroll: true,
    only: ['users'],
    data: {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value,
      role: filterRole.value,
    },
  });
};

// Fungsi untuk mengubah halaman
const changePage = (page) => {
  currentPage.value = page;
  applyFilters();
};

// Fungsi untuk mengubah jumlah item per halaman
const changePerPage = (value) => {
  perPage.value = parseInt(value);
  currentPage.value = 1; // Reset ke halaman pertama
  applyFilters();
};

// Method untuk membuka modal create
const openCreateModal = () => {
  showCreateModal.value = true;
  showPassword.value = false;
  showPasswordConfirmation.value = false;
  createForm.reset();
  createForm.clearErrors();
};

// Method untuk menutup modal create
const closeCreateModal = () => {
  showCreateModal.value = false;
  showPassword.value = false;
  showPasswordConfirmation.value = false;
  createForm.reset();
  createForm.clearErrors();
};

// Method untuk membuka modal edit
const openEditModal = (user) => {
  editForm.clearErrors();
  editForm.id = user.id;
  editForm.name = user.name;
  editForm.username = user.username;
  editForm.password = '';
  editForm.role = user.role;
  editForm.status = user.status;
  
  selectedUser.value = user;
  showEditModal.value = true;
  showEditPassword.value = false;
};

// Method untuk menutup modal edit
const closeEditModal = () => {
  showEditModal.value = false;
  showEditPassword.value = false;
  selectedUser.value = null;
  editForm.reset();
  editForm.clearErrors();
};

// Method untuk membuka modal delete
const openDeleteModal = (user) => {
  selectedUser.value = user;
  showDeleteModal.value = true;
};

// Method untuk menutup modal delete
const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedUser.value = null;
};

// Method untuk submit form create
const createUser = () => {
  createForm.post(route('admin.users.store'), {
    onSuccess: () => {
      closeCreateModal();
    }
  });
};

// Method untuk submit form edit
const updateUser = () => {
  editForm.put(route('admin.users.update', editForm.id), {
    onSuccess: () => {
      closeEditModal();
    }
  });
};

// Method untuk konfirmasi delete
const deleteUser = () => {
  if (selectedUser.value) {
    useForm().delete(route('admin.users.destroy', selectedUser.value.id), {
      onSuccess: () => {
        closeDeleteModal();
      }
    });
  }
};

// Computed untuk menampilkan range item
const displayedItemsRange = computed(() => {
  if (!props.users || !props.users.data || props.users.data.length === 0) {
    return '0-0';
  }
  const from = props.users.from || ((currentPage.value - 1) * perPage.value + 1);
  const to = props.users.to || Math.min(currentPage.value * perPage.value, props.users.total);
  return `${from}-${to}`;
});

// Computed untuk total pages
const totalPages = computed(() => {
  return props.users?.last_page || 1;
});

// Computed untuk total items
const totalItems = computed(() => {
  return props.users?.total || 0;
});
</script>

<template>
  <Head title="Admin User Management"/>

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
            User Management
          </li>
        </ol>
      </nav>
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">User Management</h1>
        <button @click="openCreateModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          <i class="fas fa-plus mr-2"></i> Tambah User
        </button>
      </div>
    </template>

    <!-- Tabel User dengan Filter dan Pagination -->
    <div class="p-6">
      <div class="bg-white rounded-lg shadow">
        <div class="p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 space-y-4 md:space-y-0">
            <h2 class="text-lg font-semibold">Daftar User</h2>
            <div class="flex flex-col md:flex-row gap-4">
              <div class="relative">
                <input
                  type="text"
                  v-model="searchQuery"
                  @keyup.enter="searchUsers"
                  placeholder="Cari nama atau username..."
                  class="w-full md:w-64 pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <div class="absolute left-3 top-2.5 text-gray-400">
                  <i class="fas fa-search"></i>
                </div>
              </div>
              <select
                v-model="filterRole"
                @change="changeFilterRole"
                class="w-full md:w-48 px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="all">Semua Role</option>
                <option value="pegawai">Pegawai</option>
                <option value="atasan">Atasan</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
              <thead>
                <tr class="bg-gray-100">
                  <th class="p-2 text-left">No</th>
                  <th class="p-2 text-left">Nama</th>
                  <th class="p-2 text-left">username</th>
                  <th class="p-2 text-left">Role</th>
                  <th class="p-2 text-left">Status</th>
                  <th class="p-2 text-left">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(user, index) in props.users?.data"
                  :key="user.id"
                  class="border-b hover:bg-gray-50 transition duration-200"
                >
                  <td class="p-2 text-sm">{{ props.users.from + index }}</td>
                  <td class="p-2">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                  </td>
                  <td class="p-2 text-sm text-gray-500">{{ user.username }}</td>
                  <td class="p-2">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800': user.role === 'pegawai',
                        'bg-purple-100 text-purple-800': user.role === 'atasan',
                        'bg-green-100 text-green-800': user.role === 'admin'
                      }"
                    >
                      {{ user.role }}
                    </span>
                  </td>
                  <td class="p-2">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="user.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                      {{ user.status }}
                    </span>
                  </td>
                  <td class="p-2">
                    <div class="flex gap-2">
                      <button @click="openEditModal(user)" class="text-blue-500 hover:text-blue-700 flex items-center transition duration-200 text-sm font-medium">
                        <i class="fas fa-edit mr-1"></i> Edit
                      </button>
                      <button @click="openDeleteModal(user)" class="text-red-500 hover:text-red-700 flex items-center transition duration-200 text-sm font-medium">
                        <i class="fas fa-trash mr-1"></i> Hapus
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!props.users?.data || props.users.data.length === 0">
                  <td colspan="6" class="p-2 text-center text-sm text-gray-500">
                    Tidak ada user yang ditemukan
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Controls -->
          <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-700">Tampilkan</span>
              <div class="relative">
                <select
                  :value="perPage"
                  @change="changePerPage($event.target.value)"
                  class="border rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white appearance-none pr-8 cursor-pointer"
                >
                  <option v-for="option in perPageOptions" :key="option" :value="option" class="py-1">
                    {{ option }}
                  </option>
                </select>
              </div>
              <span class="text-sm text-gray-700">
                item per halaman | Menampilkan {{ displayedItemsRange }} dari {{ totalItems }} item
              </span>
            </div>

            <div class="flex items-center space-x-2">
              <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded border text-sm"
                :class="currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'"
              >
                <i class="fas fa-chevron-left"></i>
              </button>

              <template v-for="page in totalPages" :key="page">
                <button
                  v-if="page === currentPage ||
                        page === 1 ||
                        page === totalPages ||
                        (page >= currentPage - 1 && page <= currentPage + 1)"
                  @click="changePage(page)"
                  class="px-3 py-1 rounded text-sm"
                  :class="currentPage === page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100'"
                >
                  {{ page }}
                </button>
                <span
                  v-else-if="page === currentPage - 2 || page === currentPage + 2"
                  class="text-gray-400"
                >
                  ...
                </span>
              </template>

              <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border text-sm"
                :class="currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'"
              >
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Create User -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
      <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold">Tambah User Baru</h2>
          <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="createUser">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nama</label>
              <input 
                type="text" 
                v-model="createForm.name" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                required
              >
              <div v-if="createForm.errors.name" class="text-red-500 text-sm mt-1">
                {{ createForm.errors.name }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Username</label>
              <input 
                type="text" 
                v-model="createForm.username" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                required
              >
              <div v-if="createForm.errors.username" class="text-red-500 text-sm mt-1">
                {{ createForm.errors.username }}
              </div>
            </div>

            <div class="relative">
              <label class="block text-sm font-medium text-gray-700">Password</label>
              <div class="relative">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="createForm.password"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10"
                  required
                >
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 mt-1"
                >
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
              <div v-if="createForm.errors.password" class="text-red-500 text-sm mt-1">
                {{ createForm.errors.password }}
              </div>
            </div>

            <div class="relative">
              <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
              <div class="relative">
                <input
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  v-model="createForm.password_confirmation"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10"
                  required
                >
                <button
                  type="button"
                  @click="showPasswordConfirmation = !showPasswordConfirmation"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 mt-1"
                >
                  <i :class="showPasswordConfirmation ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Role</label>
              <select 
                v-model="createForm.role" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="pegawai">Pegawai</option>
                <option value="atasan">Atasan</option>
                <option value="admin">Admin</option>
              </select>
              <div v-if="createForm.errors.role" class="text-red-500 text-sm mt-1">
                {{ createForm.errors.role }}
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button type="button" @click="closeCreateModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
              Batal
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              :disabled="createForm.processing"
            >
              {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Edit User -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
      <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold">Edit User</h2>
          <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="updateUser">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nama</label>
              <input 
                type="text" 
                v-model="editForm.name" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                required
              >
              <div v-if="editForm.errors.name" class="text-red-500 text-sm mt-1">
                {{ editForm.errors.name }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Username</label>
              <input 
                type="text" 
                v-model="editForm.username" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                required
              >
              <div v-if="editForm.errors.username" class="text-red-500 text-sm mt-1">
                {{ editForm.errors.username }}
              </div>
            </div>

            <div class="relative">
              <label class="block text-sm font-medium text-gray-700">
                Password
                <span class="text-sm text-gray-500">(Kosongkan jika tidak ingin mengubah)</span>
              </label>
              <div class="relative">
                <input
                  :type="showEditPassword ? 'text' : 'password'"
                  v-model="editForm.password"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10"
                >
                <button
                  type="button"
                  @click="showEditPassword = !showEditPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 mt-1"
                >
                  <i :class="showEditPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
              <div v-if="editForm.errors.password" class="text-red-500 text-sm mt-1">
                {{ editForm.errors.password }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Role</label>
              <select 
                v-model="editForm.role" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="pegawai">Pegawai</option>
                <option value="atasan">Atasan</option>
                <option value="admin">Admin</option>
              </select>
              <div v-if="editForm.errors.role" class="text-red-500 text-sm mt-1">
                {{ editForm.errors.role }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select 
                v-model="editForm.status" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
              <div v-if="editForm.errors.status" class="text-red-500 text-sm mt-1">
                {{ editForm.errors.status }}
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button type="button" @click="closeEditModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
              Batal
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              :disabled="editForm.processing"
            >
              {{ editForm.processing ? 'Mengupdate...' : 'Update' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
      <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <div class="mb-6">
          <h2 class="text-xl font-semibold mb-2">Konfirmasi Hapus</h2>
          <p class="text-gray-600">Apakah Anda yakin ingin menghapus user ini?</p>
          <p class="font-medium mt-2">{{ selectedUser?.name }}</p>
        </div>

        <div class="flex justify-end space-x-3">
          <button @click="closeDeleteModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            Batal
          </button>
          <button 
            @click="deleteUser" 
            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
