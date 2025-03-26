<script setup>
import { ref } from 'vue';
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
const modalMode = ref('add') // 'add' atau 'edit'
const formData = ref({
  id: null,
  name: '',
  position: '',
  email: '',
})

const showConfirmModal = ref(false)
const employeeToDelete = ref(null)

const selectDivision = (division) => {
  selectedDivision.value = division
}

const openAddModal = () => {
  modalMode.value = 'add'
  formData.value = {
    id: null,
    name: '',
    position: '',
    email: '',
  }
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
  selectedDivision.value.employees = selectedDivision.value.employees.filter(
    (emp) => emp.id !== employeeToDelete.value,
  )
  showConfirmModal.value = false
  employeeToDelete.value = null
}

const handleSubmit = () => {
  if (modalMode.value === 'add') {
    const newEmployee = {
      ...formData.value,
      id: Date.now(), // ID sederhana untuk contoh
    }
    selectedDivision.value.employees.push(newEmployee)
  } else {
    const index = selectedDivision.value.employees.findIndex((emp) => emp.id === formData.value.id)
    if (index !== -1) {
      selectedDivision.value.employees[index] = { ...formData.value }
    }
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

    <div class="flex gap-6">
      <!-- Daftar Divisi -->
      <div class="w-1/4 bg-white p-4 rounded-lg shadow">
        <h2 class="font-semibold text-lg">Divisi</h2>
        <div
          v-for="division in divisions"
          :key="division.id"
          class="p-3 cursor-pointer rounded-lg hover:bg-gray-100 transition duration-200"
          :class="{ 'bg-blue-600 text-white': selectedDivision?.id === division.id }"
          @click="selectDivision(division)"
        >
          <span class="flex items-center">
            <i class="fas fa-building mr-2"></i>
            {{ division.name }} ({{ division.employees.length }})
          </span>
        </div>
      </div>

      <!-- Daftar Karyawan -->
      <div class="flex-1 bg-white p-6 rounded-lg shadow">
        <div v-if="selectedDivision">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">{{ selectedDivision.name }}</h2>
            <button class="bg-blue-600 text-white px-4 py-2 rounded flex items-center" @click="openAddModal">
              <i class="fas fa-plus mr-2"></i> Tambah Karyawan
            </button>
          </div>
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-2 text-left">Nama</th>
                <th class="p-2 text-left">Posisi</th>
                <th class="p-2 text-left">Email</th>
                <th class="p-2">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in selectedDivision.employees" :key="employee.id" class="border-b hover:bg-gray-50 transition duration-200">
                <td class="p-2">{{ employee.name }}</td>
                <td class="p-2">{{ employee.position }}</td>
                <td class="p-2">{{ employee.email }}</td>
                <td class="p-2 flex gap-2">
                  <button class="text-blue-500 flex items-center" @click="openEditModal(employee)">
                    <i class="fas fa-edit mr-1"></i> Edit
                  </button>
                  <button class="text-red-500 flex items-center" @click="deleteEmployee(employee.id)">
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

<style scoped>
.employees-page {
  padding: 20px;
}

.content-layout {
  display: flex;
  gap: 30px;
  margin-top: 20px;
}

.divisions-list {
  width: 300px;
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.division-item {
  display: flex;
  align-items: center;
  padding: 15px;
  margin: 5px 0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.division-item:hover {
  background: #f5f5f5;
}

.division-item.active {
  background: #1a237e;
  color: white;
}

.division-item i {
  margin-right: 10px;
}

.employee-count {
  margin-left: auto;
  background: rgba(0, 0, 0, 0.1);
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.9em;
}

.employees-list {
  flex: 1;
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

th,
td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.add-btn {
  background: #1a237e;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px 10px;
}

.action-btn.edit {
  color: #2196f3;
}

.action-btn.delete {
  color: #f44336;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 500px;
  max-width: 90%;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.form-actions button {
  padding: 8px 20px;
  border-radius: 4px;
  cursor: pointer;
}

.submit-btn {
  background: #1a237e;
  color: white;
  border: none;
}

button:hover {
  opacity: 0.9;
}

.no-selection {
  text-align: center;
  padding: 50px;
  color: #666;
}

.no-selection i {
  font-size: 3em;
  margin-bottom: 20px;
  color: #1a237e;
}

.confirmation-modal {
  max-width: 400px;
  text-align: center;
}

.confirmation-modal p {
  margin: 20px 0;
  color: #666;
}

.delete-btn {
  background: #f44336;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 4px;
  cursor: pointer;
}

.delete-btn:hover {
  background: #d32f2f;
}
</style>
