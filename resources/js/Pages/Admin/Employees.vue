<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
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
const showModal = ref(false)
const modalMode = ref('add')
const formData = ref({ id: null, name: '', position: '', email: '' })

const showConfirmModal = ref(false)
const employeeToDelete = ref(null)

const searchQuery = ref('')

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
  modalMode.value = 'add'
  formData.value = { id: null, name: '', position: '', email: '' }
  showModal.value = true
}
const openEditModal = (employee) => {
  modalMode.value = 'edit'
  formData.value = { ...employee }
  showModal.value = true
}
const deleteEmployee = (employeeId) => {
  employeeToDelete.value = employeeId
  showConfirmModal.value = true
}
const confirmDelete = () => {
  selectedDivision.value.employees = selectedDivision.value.employees.filter(emp => emp.id !== employeeToDelete.value)
  showConfirmModal.value = false
  employeeToDelete.value = null
}
const handleSubmit = () => {
  if (modalMode.value === 'add') {
    const newEmployee = { ...formData.value, id: Date.now() }
    selectedDivision.value.employees.push(newEmployee)
  } else {
    const index = selectedDivision.value.employees.findIndex(emp => emp.id === formData.value.id)
    if (index !== -1) selectedDivision.value.employees[index] = { ...formData.value }
  }
  showModal.value = false
}
</script>

<template>
  <Head title="Admin Employees"/>

  <AuthenticatedLayout>
    <template #header>
      <h1 class="text-xl font-semibold text-gray-900">Daftar Karyawan</h1>
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
                v-for="employee in filteredEmployees"
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
        </div>
        <div v-else class="text-center text-gray-500 py-10">
          <p>Pilih divisi untuk melihat daftar karyawan</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
