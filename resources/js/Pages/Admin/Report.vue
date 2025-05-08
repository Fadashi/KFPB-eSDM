<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

// State untuk modal download laporan absensi
const showAbsensiModal = ref(false);
const selectedEmployeeId = ref('');
const startDate = ref('');
const endDate = ref('');
const downloadFormat = ref('pdf');
const employees = ref([]);
const isLoading = ref(false);
const searchQuery = ref('');
const isDropdownOpen = ref(false);
const selectedEmployee = ref(null);

// Computed property untuk memfilter karyawan berdasarkan pencarian
const filteredEmployees = computed(() => {
  if (!searchQuery.value) return employees.value;
  
  const query = searchQuery.value.toLowerCase();
  return employees.value.filter(emp => 
    emp.name.toLowerCase().includes(query) || 
    emp.nip.toLowerCase().includes(query)
  );
});

// Referensi global untuk gambar logo yang akan digunakan dalam PDF
const logoDataUrl = ref('');

// Fungsi untuk mengambil data karyawan
const fetchEmployees = async () => {
  try {
    isLoading.value = true;
    const response = await axios.get('/api/employees');
    employees.value = response.data.employees;
  } catch (error) {
    console.error('Error mengambil data karyawan:', error);
  } finally {
    isLoading.value = false;
  }
};

// Fungsi untuk mengonversi gambar ke data URL
const convertImageToDataUrl = () => {
  const img = new Image();
  img.crossOrigin = 'Anonymous';
  img.onload = function() {
    const canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    logoDataUrl.value = canvas.toDataURL('image/png');
  };
  img.src = '/images/KF_Logo.png';
};

// Panggil fungsi konversi dan ambil data karyawan saat component mounted
onMounted(() => {
  convertImageToDataUrl();
  fetchEmployees();

  // Click outside to close dropdown
  document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('employee-dropdown');
    if (dropdown && !dropdown.contains(e.target)) {
      isDropdownOpen.value = false;
    }
  });
});

// Fungsi untuk membuka modal download absensi
const openAbsensiModal = () => {
  showAbsensiModal.value = true;
  searchQuery.value = '';
  selectedEmployee.value = null;
};

// Fungsi untuk menutup modal download absensi
const closeAbsensiModal = () => {
  showAbsensiModal.value = false;
};

// Fungsi untuk memilih karyawan
const selectEmployee = (employee) => {
  selectedEmployeeId.value = employee.id;
  selectedEmployee.value = employee;
  isDropdownOpen.value = false;
  searchQuery.value = '';
};

// Fungsi untuk memformat tanggal (yyyy-MM-dd ke dd Mmm yyyy)
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const day = date.getDate();
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();
  return `${day} ${month} ${year}`;
};

// Fungsi untuk mendapatkan data karyawan yang dipilih
const getSelectedEmployee = () => {
  return employees.value.find(emp => emp.id.toString() === selectedEmployeeId.value.toString());
};

// Fungsi untuk download laporan absensi
const downloadAbsensiReport = () => {
  const employee = getSelectedEmployee();
  
  if (!employee && selectedEmployeeId.value) {
    alert('Karyawan tidak ditemukan');
    return;
  }
  
  // Buat konten HTML untuk laporan
  let reportContent = `
    <html>
    <head>
      <title>Laporan Absensi Pegawai</title>
      <style>
        @page {
          size: A4;
          margin: 1.5cm 1cm;
        }
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 0;
          color: #333;
          font-size: 12px;
        }
        .header {
          text-align: center;
          border-bottom: 2px solid #000;
          padding-bottom: 10px;
          margin-bottom: 20px;
          position: relative;
        }
        .logo {
          position: absolute;
          top: 0;
          left: 0;
          height: 60px;
        }
        .header h1 {
          margin: 0;
          font-size: 16px;
          font-weight: bold;
          color: #1a5f7a;
        }
        .header h2 {
          margin: 5px 0;
          font-size: 14px;
          font-weight: bold;
          color: #2c3e50;
        }
        .header p {
          margin: 3px 0;
          font-size: 11px;
          color: #555;
        }
        .info {
          margin: 20px 0;
        }
        .info p {
          margin: 5px 0;
          font-size: 12px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin: 20px 0;
        }
        th, td {
          border: 1px solid #ddd;
          padding: 8px;
          text-align: left;
          font-size: 11px;
        }
        th {
          background-color: #f2f2f2;
          font-weight: bold;
        }
        tr:nth-child(even) {
          background-color: #f9f9f9;
        }
        .footer {
          margin-top: 30px;
          text-align: right;
        }
        .signature {
          margin-top: 50px;
        }
        .page-footer {
          position: fixed;
          bottom: 0;
          left: 0;
          right: 0;
          padding: 10px;
          text-align: center;
          font-size: 10px;
          color: #666;
          border-top: 1px solid #eee;
        }
        .page-number:before {
          content: "Halaman " counter(page);
        }
      </style>
    </head>
    <body>
      <!-- Header (Kop Surat) -->
      <div class="header">
        <img src="${logoDataUrl.value || '/images/KF_Logo.png'}" alt="Logo Kimia Farma" class="logo">
        <h1>PT. KIMIA FARMA PLANT BANJARAN</h1>
        <h2>LAPORAN ABSENSI PEGAWAI</h2>
        <p>Jl. Raya Banjaran KM.16, Kab.Bandung, Jawa Barat</p>
        <p>Web: https://sdm.e-kfpb.com Email: plant_bandung@kimiafarma.co.id</p>
      </div>

      <!-- Info Laporan -->
      <div class="info">
        <p>
          <strong>Nama:</strong> ${employee ? employee.name + '-' + employee.nip : 'Semua Karyawan'}
        </p>
        <p>
          <strong>Periode:</strong> ${startDate.value || 'N/A'} s/d ${endDate.value || 'N/A'}
        </p>
      </div>
      
      <!-- Tabel Laporan -->
      <table>
        <thead>
          <tr>
            <th>NO</th>
            <th>SHIFT</th>
            <th>TANGGAL</th>
            <th>CHECK IN</th>
            <th>CHECK OUT</th>
            <th>LEMBUR</th>
            <th>KETERANGAN</th>
          </tr>
        </thead>
        <tbody>
          <!-- Data absensi akan ditampilkan di sini -->
          <tr>
            <td colspan="7" style="text-align: center;">Data absensi akan ditampilkan berdasarkan filter yang dipilih.</td>
          </tr>
        </tbody>
      </table>
      
      <!-- Footer dengan tanda tangan -->
      <div class="footer">
        <p>Bandung, ${formatDate(new Date().toISOString())}</p>
        <p>Bagian SDM</p>
        <div class="signature"></div>
        <p>(.............................)</p>
      </div>
      
      <!-- Footer di setiap halaman -->
      <div class="page-footer">
        <div>Sistem ESDM Kimia Farma Plant Banjaran 2.0</div>
        <div class="page-number"></div>
      </div>
    </body>
    </html>
  `;
  
  // Buat blob dan buka di tab baru untuk PDF
  const blob = new Blob([reportContent], { type: 'text/html' });
  const url = URL.createObjectURL(blob);
  const reportWindow = window.open(url, '_blank');
  
  // Trigger print dialog untuk save as PDF
  reportWindow.onload = () => {
    setTimeout(() => {
      reportWindow.print();
    }, 1000);
  };
  
  // Tutup modal
  closeAbsensiModal();
};

</script>

<template>
  <Head title="Admin Reports"/>

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
                        Laporan
                    </li>
                </ol>
            </nav>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Laporan</h1>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-center">
                    <!-- Card Laporan Absensi -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full max-w-lg">
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">Laporan Absensi</h3>
                                    <p class="text-sm text-gray-500 mt-1">Lihat dan download laporan kehadiran karyawan</p>
                                </div>
                                <div class="bg-blue-100 p-4 rounded-full">
                                    <i class="fas fa-calendar-check text-blue-600 text-2xl"></i>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6">
                                Laporan ini berisi data kehadiran karyawan termasuk jam masuk, jam keluar, status kehadiran, dan informasi lembur. 
                                Anda dapat memilih karyawan dan rentang tanggal untuk mengunduh laporan dalam format PDF.
                            </p>
                            <button 
                                @click="openAbsensiModal" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg flex items-center justify-center transition-colors"
                            >
                                <i class="fas fa-download mr-2"></i>
                                Download Laporan Absensi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Download Laporan Absensi -->
        <div v-if="showAbsensiModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Download Laporan Absensi</h3>
                    <button @click="closeAbsensiModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Karyawan</label>
                        <div v-if="isLoading" class="text-gray-500 text-sm">Memuat data karyawan...</div>
                        <div v-else class="relative" id="employee-dropdown">
                            <!-- Custom dropdown -->
                            <div 
                                @click="isDropdownOpen = !isDropdownOpen" 
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center cursor-pointer bg-white"
                            >
                                <span v-if="selectedEmployee">{{ selectedEmployee.name }} - {{ selectedEmployee.nip }}</span>
                                <span v-else class="text-gray-500">-- Pilih Karyawan --</span>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                            
                            <!-- Dropdown menu -->
                            <div v-if="isDropdownOpen" class="absolute z-10 mt-1 w-full bg-white border rounded-md shadow-lg max-h-60 overflow-auto">
                                <div class="sticky top-0 bg-white p-2 border-b">
                                    <div class="relative">
                                        <input
                                            type="text"
                                            v-model="searchQuery"
                                            placeholder="Cari nama atau NIP karyawan..."
                                            class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pl-8"
                                            @click.stop
                                        />
                                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400 text-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="filteredEmployees.length === 0" class="p-3 text-sm text-gray-500 text-center">
                                    Tidak ada karyawan yang ditemukan
                                </div>
                                
                                <div v-else>
                                    <div 
                                        v-for="employee in filteredEmployees" 
                                        :key="employee.id" 
                                        @click="selectEmployee(employee)"
                                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                                        :class="{'bg-blue-50': selectedEmployeeId === employee.id}"
                                    >
                                        {{ employee.name }} - {{ employee.nip }}
                                    </div>
                                </div>
                                
                                <div class="p-2 border-t text-xs text-gray-500 text-center">
                                    Menampilkan {{ filteredEmployees.length }} dari {{ employees.length }} karyawan
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input 
                            type="date" 
                            v-model="startDate"
                            class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input 
                            type="date" 
                            v-model="endDate"
                            class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="downloadFormat" value="pdf" class="form-radio h-4 w-4 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">PDF</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="downloadFormat" value="print" class="form-radio h-4 w-4 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Print</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button 
                            @click="closeAbsensiModal"
                            class="px-4 py-2 border rounded-md text-sm text-gray-700 hover:bg-gray-100"
                        >
                            Batal
                        </button>
                        <button 
                            @click="downloadAbsensiReport"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                            :disabled="isLoading || (!selectedEmployeeId)"
                        >
                            Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

  </AuthenticatedLayout>
</template>

<style scoped></style>
