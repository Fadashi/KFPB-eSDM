<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Ambil data audit trail dari server via Inertia
const page = usePage();
const auditLogs = ref(page.props.audits || []);

// Fungsi untuk memformat tanggal
const formatDate = (dateString) => {
  const date = new Date(dateString);
  const day = date.getDate();
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day} ${month} ${year} ${hours}:${minutes}`;
};

// Referensi global untuk gambar logo yang akan digunakan dalam PDF
const logoDataUrl = ref('');

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

// Panggil fungsi konversi saat component mounted
onMounted(() => {
  convertImageToDataUrl();
});

// State untuk filter dan pencarian
const searchQuery = ref('');
const filterAction = ref('all');

// State untuk filter tanggal
const startDate = ref('');
const endDate = ref('');

// State untuk modal download
const showDownloadModal = ref(false);
const downloadStartDate = ref('');
const downloadEndDate = ref('');
const downloadFilterAction = ref('all');
const downloadFormat = ref('pdf');

// State untuk pagination
const currentPage = ref(1);
const perPage = ref(10);
const perPageOptions = [5, 10, 20, 50];

// Computed property untuk filtered logs
const filteredLogs = computed(() => {
  return auditLogs.value.filter(log => {
    const q = searchQuery.value.toLowerCase();
    const matchesSearch =
      log.id.toString().includes(q) ||
      (log.created_at || '').toLowerCase().includes(q) ||
      (log.user && log.user.name ? log.user.name.toLowerCase().includes(q) : false) ||
      (log.user && log.user.role ? log.user.role.toLowerCase().includes(q) : false) ||
      (log.aksi || '').toLowerCase().includes(q) ||
      (log.model || '').toLowerCase().includes(q) ||
      (log.data || '').toLowerCase().includes(q) ||
      (log.value_asal || '').toLowerCase().includes(q) ||
      (log.value_baru || '').toLowerCase().includes(q);
    const matchesAction = filterAction.value === 'all' || log.aksi === filterAction.value;
    
    // Filter berdasarkan tanggal jika ada
    let matchesDate = true;
    if (startDate.value && log.created_at) {
      const logDate = new Date(log.created_at);
      const filterStartDate = new Date(startDate.value);
      matchesDate = matchesDate && logDate >= filterStartDate;
    }
    if (endDate.value && log.created_at) {
      const logDate = new Date(log.created_at);
      const filterEndDate = new Date(endDate.value);
      // Tambahkan 1 hari ke endDate untuk mencakup seluruh hari yang dipilih
      filterEndDate.setDate(filterEndDate.getDate() + 1);
      matchesDate = matchesDate && logDate < filterEndDate;
    }
    
    return matchesSearch && matchesAction && matchesDate;
  });
});

// Computed property untuk total items
const totalItems = computed(() => {
  return filteredLogs.value.length;
});

// Computed property untuk total halaman
const totalPages = computed(() => {
  return Math.ceil(filteredLogs.value.length / perPage.value);
});

// Computed property untuk range items yang ditampilkan
const displayedItemsRange = computed(() => {
  const start = (currentPage.value - 1) * perPage.value + 1;
  const end = Math.min(currentPage.value * perPage.value, totalItems.value);
  return `${start}-${end}`;
});

// Computed property untuk logs yang ditampilkan sesuai pagination
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredLogs.value.slice(start, end);
});

// Fungsi untuk mengubah halaman
const changePage = (page) => {
  currentPage.value = page;
};

// Fungsi untuk mengubah jumlah item per halaman
const changePerPage = (value) => {
  perPage.value = parseInt(value);
  currentPage.value = 1; // Reset ke halaman pertama
};

// Fungsi untuk membuka modal download
const openDownloadModal = () => {
  downloadStartDate.value = startDate.value;
  downloadEndDate.value = endDate.value;
  downloadFilterAction.value = filterAction.value;
  showDownloadModal.value = true;
};

// Fungsi untuk menutup modal download
const closeDownloadModal = () => {
  showDownloadModal.value = false;
};

// Fungsi untuk download laporan
const downloadReport = () => {
  // Filter logs berdasarkan kriteria download
  const logsToDownload = auditLogs.value.filter(log => {
    const matchesAction = downloadFilterAction.value === 'all' || log.aksi === downloadFilterAction.value;
    
    // Filter berdasarkan tanggal jika ada
    let matchesDate = true;
    if (downloadStartDate.value && log.created_at) {
      const logDate = new Date(log.created_at);
      const filterStartDate = new Date(downloadStartDate.value);
      matchesDate = matchesDate && logDate >= filterStartDate;
    }
    if (downloadEndDate.value && log.created_at) {
      const logDate = new Date(log.created_at);
      const filterEndDate = new Date(downloadEndDate.value);
      filterEndDate.setDate(filterEndDate.getDate() + 1);
      matchesDate = matchesDate && logDate < filterEndDate;
    }
    
    return matchesAction && matchesDate;
  });
  
  // Buat konten HTML untuk laporan
  let reportContent = `
    <html>
    <head>
      <title>Laporan Audit Trail</title>
      <style>
        @page {
          size: A4;
          margin: 0;
        }
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 0;
          color: #333;
        }
        .cover {
          height: 100vh;
          padding: 40px;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          text-align: center;
          background: linear-gradient(to bottom, #ffffff, #f8f9fa);
          page-break-after: always;
        }
        .cover img {
          width: 200px;
          margin-bottom: 40px;
        }
        .cover h1 {
          font-size: 24px;
          font-weight: bold;
          margin: 20px 0;
          color: #1a5f7a;
        }
        .cover h2 {
          font-size: 20px;
          margin: 10px 0;
          color: #2c3e50;
        }
        .cover p {
          font-size: 16px;
          margin: 10px 0;
          color: #666;
        }
        .content {
          padding: 30px;
        }
        .header {
          padding: 20px 30px;
          border-bottom: 2px solid #eee;
          margin-bottom: 20px;
        }
        .header img {
          height: 40px;
        }
        .footer {
          position: fixed;
          bottom: 0;
          left: 0;
          right: 0;
          padding: 15px 30px;
          background: #f8f9fa;
          border-top: 1px solid #eee;
          font-size: 12px;
          color: #666;
          text-align: center;
        }
        .page-number:before {
          content: "Halaman " counter(page);
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin: 20px 0;
        }
        th, td {
          padding: 12px;
          text-align: left;
          border-bottom: 1px solid #ddd;
          font-size: 14px;
        }
        th {
          background-color: #f8f9fa;
          font-weight: bold;
          color: #2c3e50;
        }
        tr:nth-child(even) {
          background-color: #f9f9f9;
        }
        .report-info {
          margin: 20px 0;
          padding: 15px;
          background: #f8f9fa;
          border-radius: 5px;
        }
        .report-info p {
          margin: 5px 0;
          font-size: 14px;
        }
        @media print {
          .page-break {
            page-break-before: always;
          }
        }
      </style>
    </head>
    <body>
      <!-- Cover Page -->
      <div class="cover">
        <img src="${logoDataUrl.value || '/images/KF_Logo.png'}" alt="Logo Kimia Farma">
        <h1>PT. KIMIA FARMA PLANT BANJARAN</h1>
        <h2>LAPORAN AUDIT TRAIL</h2>
        <h2>Sistem Manajemen SDM Kimia Farma Plant Banjaran 2.0</h2>
        <p>Jl. Raya Banjaran KM.16, Kab.Bandung, Jawa Barat</p>
      </div>

      <!-- Content Pages -->
      <div class="content">
        <!-- Header di setiap halaman -->
        <div class="header">
          <img src="${logoDataUrl.value || '/images/KF_Logo.png'}" alt="Logo Kimia Farma">
        </div>

        <div class="report-info">
          <h2 style="margin-top: 0;">Laporan Audit Trail</h2>
          <p>
            <strong>Periode:</strong> ${downloadStartDate.value ? new Date(downloadStartDate.value).toLocaleDateString('id-ID') : 'Semua'} 
            ${downloadEndDate.value ? ' - ' + new Date(downloadEndDate.value).toLocaleDateString('id-ID') : ''}
          </p>
          <p><strong>Filter Aksi:</strong> ${downloadFilterAction.value === 'all' ? 'Semua' : downloadFilterAction.value}</p>
          <p><strong>Total Entri:</strong> ${logsToDownload.length}</p>
          <p><strong>Tanggal Cetak:</strong> ${new Date().toLocaleString('id-ID')}</p>
        </div>
        
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Waktu</th>
              <th>Nama</th>
              <th>Role</th>
              <th>Aksi</th>
              <th>Data</th>
              <th>Value Asal</th>
              <th>Value Baru</th>
            </tr>
          </thead>
          <tbody>
  `;
  
  // Tambahkan data ke laporan
  logsToDownload.forEach((log, index) => {
    // Bangun HTML untuk kolom Value Asal dan Value Baru
    const changedKeys = getChangedKeys(log);
    const oldHtml = changedKeys.length
      ? changedKeys.map(k => `${labelKey(k)}: ${getOldValue(log, k)}`).join('<br/>')
      : '-';
    const newHtml = changedKeys.length
      ? changedKeys.map(k => `${labelKey(k)}: ${getNewValue(log, k)}`).join('<br/>')
      : '-';
    reportContent += `
      <tr>
        <td>${index + 1}</td>
        <td>${log.created_at ? formatDate(log.created_at) : '-'}</td>
        <td>${log.user && log.user.name ? log.user.name : '-'}</td>
        <td>${log.user && log.user.role ? log.user.role : '-'}</td>
        <td>${log.aksi ? log.aksi.charAt(0).toUpperCase() + log.aksi.slice(1) : '-'}</td>
        <td>${log.data || '-'}</td>
        <td>${oldHtml}</td>
        <td>${newHtml}</td>
      </tr>
    `;
  });
  
  // Tutup tabel dan tambahkan footer
  reportContent += `
          </tbody>
        </table>
      </div>

      <!-- Footer di setiap halaman -->
      <div class="footer">
        <div style="float: left;">PT. Kimia Farma Plant Banjaran</div>
        <div style="float: right;" class="page-number"></div>
        <div style="clear: both;"></div>
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
  closeDownloadModal();
};

// Helper untuk cek apakah string merupakan ISO date
const isIsoDate = (str) => {
  if (typeof str !== 'string') return false;
  const d = new Date(str);
  return !isNaN(d.getTime()) && /\d{4}-\d{2}-\d{2}/.test(str);
};

// Fungsi untuk memformat tanggal saja tanpa waktu
const formatDateOnly = (dateString) => {
  const date = new Date(dateString);
  const day = date.getDate();
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();
  return `${day} ${month} ${year}`;
};

// Fungsi untuk mendapatkan nilai lama berdasarkan key
const getOldValue = (log, key) => {
  const obj = log.value_asal ? JSON.parse(log.value_asal) : {};
  let val = obj[key] != null ? obj[key] : '-';
  if (isIsoDate(val)) val = formatDateOnly(val);
  return val;
};

// Fungsi untuk mendapatkan nilai baru berdasarkan key
const getNewValue = (log, key) => {
  const obj = log.value_baru ? JSON.parse(log.value_baru) : {};
  let val = obj[key] != null ? obj[key] : '-';
  if (isIsoDate(val)) val = formatDateOnly(val);
  return val;
};

// Fungsi untuk merubah key seperti "nama_libur" menjadi "Nama Libur"
const labelKey = (key) => key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

// Fungsi untuk mendapatkan semua key yang diubah (kecuali id, created_at, updated_at)
const getChangedKeys = (log) => {
  const newObj = log.value_baru ? JSON.parse(log.value_baru) : {};
  const oldObj = log.value_asal ? JSON.parse(log.value_asal) : {};
  const filter = (obj) => Object.keys(obj).filter(k => !['id','created_at','updated_at'].includes(k));
  const newKeys = filter(newObj);
  const oldKeys = filter(oldObj);
  return Array.from(new Set([...oldKeys, ...newKeys]));
};
</script>

<template>
  <Head title="Admin Audit Trail" />

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
            Audit Trail
          </li>
        </ol>
      </nav>

      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Audit Trail</h1>
      </div>
    </template>

    <div class="bg-white p-6 rounded-lg shadow mt-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 space-y-4 md:space-y-0">
        <h2 class="text-lg font-semibold">Riwayat Audit</h2>
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Tombol Download Report -->
          <button 
            @click="openDownloadModal"
            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center"
          >
            <i class="fas fa-download mr-2"></i>
            Download Laporan
          </button>
        </div>
      </div>
      
      <!-- Filter Section -->
      <div class="flex flex-col md:flex-row gap-4 mb-4">
        <div class="relative">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari riwayat audit..."
            class="w-full md:w-64 pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
          <div class="absolute left-3 top-2.5 text-gray-400">
            <i class="fas fa-search"></i>
          </div>
        </div>
        
        <select
          v-model="filterAction"
          class="w-full md:w-48 px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="all">Semua Aksi</option>
          <option value="tambah">Tambah</option>
          <option value="ubah">Ubah</option>
          <option value="hapus">Hapus</option>
        </select>
        
        <div class="flex flex-col md:flex-row gap-2 items-center">
          <input
            type="date"
            v-model="startDate"
            class="w-full md:w-40 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Tanggal Mulai"
          >
          <span class="text-sm text-gray-600 px-1">s/d</span>
          <input
            type="date"
            v-model="endDate"
            class="w-full md:w-40 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Tanggal Akhir"
          >
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full border-collapse">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 text-left">No</th>
              <th class="p-2 text-left">Waktu</th>
              <th class="p-2 text-left">Nama</th>
              <th class="p-2 text-left">Role</th>
              <th class="p-2 text-left">Aksi</th>
              <th class="p-2 text-left">Data</th>
              <th class="p-2 text-left">Value Asal</th>
              <th class="p-2 text-left">Value Baru</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(log, index) in paginatedLogs"
              :key="log.id"
              class="border-b hover:bg-gray-50 transition duration-200"
            >
              <td class="p-2 text-sm">{{ (currentPage - 1) * perPage + index + 1 }}</td>
              <td class="p-2 text-sm">{{ log.created_at ? formatDate(log.created_at) : '-' }}</td>
              <td class="p-2 text-sm">{{ log.user && log.user.name ? log.user.name : '-' }}</td>
              <td class="p-2 text-sm">{{ log.user && log.user.role ? log.user.role : '-' }}</td>
              <td class="p-2">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': log.aksi === 'tambah',
                    'bg-blue-100 text-blue-800': log.aksi === 'ubah',
                    'bg-red-100 text-red-800': log.aksi === 'hapus'
                  }"
                >
                  {{ log.aksi ? log.aksi.charAt(0).toUpperCase() + log.aksi.slice(1) : '-' }}
                </span>
              </td>
              <td class="p-2 text-sm">{{ log.data || '-' }}</td>
              <td class="p-2 text-sm">
                <template v-if="log.value_asal">
                  <div v-for="key in getChangedKeys(log)" :key="key">
                    <strong>{{ labelKey(key) }}:</strong> {{ getOldValue(log, key) }}
                  </div>
                </template>
                <template v-else>-
                </template>
              </td>
              <td class="p-2 text-sm">
                <template v-if="log.value_baru">
                  <div v-for="key in getChangedKeys(log)" :key="key">
                    <strong>{{ labelKey(key) }}:</strong> {{ getNewValue(log, key) }}
                  </div>
                </template>
                <template v-else>-
                </template>
              </td>
            </tr>
            <tr v-if="paginatedLogs.length === 0">
              <td colspan="8" class="p-2 text-center text-sm text-gray-500">
                Tidak ada riwayat audit yang ditemukan
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
    
    <!-- Modal Download Report -->
    <div v-if="showDownloadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Download Laporan Audit Trail</h3>
          <button @click="closeDownloadModal" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input 
              type="date" 
              v-model="downloadStartDate"
              class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input 
              type="date" 
              v-model="downloadEndDate"
              class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Aksi</label>
            <select
              v-model="downloadFilterAction"
              class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="all">Semua Aksi</option>
              <option value="tambah">Tambah</option>
              <option value="ubah">Ubah</option>
              <option value="hapus">Hapus</option>
            </select>
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
              @click="closeDownloadModal"
              class="px-4 py-2 border rounded-md text-sm text-gray-700 hover:bg-gray-100"
            >
              Batal
            </button>
            <button 
              @click="downloadReport"
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
            >
              Download PDF
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
