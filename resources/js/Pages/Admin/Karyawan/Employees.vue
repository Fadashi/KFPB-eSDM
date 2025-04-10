<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const divisions = ref([
  {
    id: 1,
    name: 'Divisi IT',
    employees: [
      { id: 1, name: 'Budi Santoso', position: 'Frontend Developer', email: 'budi@example.com' },
      { id: 2, name: 'Dedi Kurniawan', position: 'Backend Developer', email: 'dedi@example.com' },
      { id: 3, name: 'Rini Susanti', position: 'UI/UX Designer', email: 'rini@example.com' },
    ],
  },
  {
    id: 2,
    name: 'Divisi HR',
    employees: [
      { id: 4, name: 'Siti Rahayu', position: 'HR Manager', email: 'siti@example.com' },
      { id: 5, name: 'Ahmad Rizki', position: 'HR Staff', email: 'ahmad@example.com' },
    ],
  },
  {
    id: 3,
    name: 'Divisi Marketing',
    employees: [
      { id: 6, name: 'Diana Putri', position: 'Marketing Manager', email: 'diana@example.com' },
      { id: 7, name: 'Eko Prasetyo', position: 'Marketing Staff', email: 'eko@example.com' },
    ],
  },
])

const selectedDivision = ref(null)
const searchQuery = ref('')

// Tambahan state untuk pagination
const currentPage = ref(1);
const itemsPerPage = 7; // Fixed items per page

// Computed property untuk total items
const totalItems = computed(() => {
  return filteredEmployees.value.length;
});

// Computed property untuk total halaman
const totalPages = computed(() => {
  return Math.ceil(totalItems.value / itemsPerPage);
});

// Computed property untuk range items yang ditampilkan
const displayedItemsRange = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage + 1;
  const end = Math.min(currentPage.value * itemsPerPage, totalItems.value);
  return `${start}-${end}`;
});

// Computed property untuk employees yang ditampilkan sesuai pagination
const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredEmployees.value.slice(start, end);
});

// Fungsi untuk mengubah halaman
const changePage = (page) => {
  currentPage.value = page;
};

const filteredEmployees = computed(() => {
  if (!selectedDivision.value) return []
  if (!searchQuery.value) return selectedDivision.value.employees

  const query = searchQuery.value.toLowerCase()
  return selectedDivision.value.employees.filter(employee => 
    employee.name.toLowerCase().includes(query) ||
    employee.position.toLowerCase().includes(query) ||
    employee.email.toLowerCase().includes(query)
  )
})

const selectDivision = (division) => selectedDivision.value = division
const openAddModal = () => {
  router.visit('/admin/employees/create')
}
const openEditModal = (employee) => {
  router.visit(`/admin/employees/${employee.id}/edit`)
}
const deleteEmployee = (employeeId) => {
  if (confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
    selectedDivision.value.employees = selectedDivision.value.employees.filter(emp => emp.id !== employeeId)
  }
}
</script>

<template>
  <Head title="Admin Employees"/>

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
                        Karyawan
                    </li>
                </ol>
            </nav>

            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Karyawan</h1>
            </div>
        </template>

    <div class="flex gap-6 mt-6">
      <!-- Divisi -->
      <div class="w-1/4 bg-white p-4 rounded-lg shadow">
        <h2 class="font-semibold text-lg mb-2">Divisi</h2>
        <div
          v-for="division in divisions"
          :key="division.id"
          class="p-3 cursor-pointer rounded-lg transition duration-200 flex items-center justify-between"
          :class="{ 'bg-blue-600 text-white': selectedDivision?.id === division.id, 'hover:bg-gray-100': selectedDivision?.id !== division.id }"
          @click="selectDivision(division)"
        >
          <span class="flex items-center">
            <i class="fas fa-building mr-2"></i>
            {{ division.name }}
          </span>
          <span class="bg-black/10 px-2 py-0.5 text-xs rounded-full" :class="{ 'bg-white/20 text-white': selectedDivision?.id === division.id }">
            {{ division.employees.length }}
          </span>
        </div>
      </div>

      <!-- Karyawan -->
      <div class="flex-1 bg-white p-6 rounded-lg shadow">
        <div v-if="selectedDivision">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">{{ selectedDivision.name }}</h2>
            <div class="flex items-center gap-4">
              <div class="relative">
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Cari karyawan..."
                  class="pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
              </div>
              <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center" @click="openAddModal">
                <i class="fas fa-plus mr-2"></i> Tambah Karyawan
              </button>
            </div>
          </div>

          <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-2">Nama</th>
                <th class="p-2">Posisi</th>
                <th class="p-2">Email</th>
                <th class="p-2 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="employee in paginatedEmployees"
                :key="employee.id"
                class="border-b hover:bg-gray-50 transition duration-200"
              >
                <td class="p-2">{{ employee.name }}</td>
                <td class="p-2">{{ employee.position }}</td>
                <td class="p-2">{{ employee.email }}</td>
                <td class="p-2 text-center">
                  <button class="text-blue-600 hover:underline mr-2" @click="openEditModal(employee)">
                    <i class="fas fa-edit mr-1"></i> Edit
                  </button>
                  <button class="text-red-600 hover:underline" @click="deleteEmployee(employee.id)">
                    <i class="fas fa-trash mr-1"></i> Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination dan Info -->
          <div class="mt-4 flex items-center justify-between border-t pt-4">
            <div class="text-sm text-gray-700">
              Menampilkan {{ displayedItemsRange }} dari {{ totalItems }} karyawan
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
        <div v-else class="text-center text-gray-500 py-10">
          <p>Pilih divisi untuk melihat daftar karyawan</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template> 