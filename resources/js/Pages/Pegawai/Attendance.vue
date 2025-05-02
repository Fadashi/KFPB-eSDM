<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import axios from 'axios'

// Koordinat PT Kimia Farma Tbk Plant Banjaran
const PT_COORDINATES = {
  latitude: -7.039,  // 7°02'20.4"S
  longitude: 107.596, // 107°35'45.8"E
  radius: 2 // radius dalam kilometer
}

const currentTime = ref('')
const currentDate = ref('')
const attendance = ref({
  status: 'Belum Absen',
  check_in: null,
  check_out: null,
  location: null,
  shift: 'non_shift'
})
const monthlyAttendance = ref([])
const statistics = ref({
  total_days: 30,
  present_days: 0,
  late_days: 0,
  leave_days: 0,
  percentages: {
    present: 0,
    late: 0,
    leave: 0
  }
})
const mapRef = ref(null)
const map = ref(null)
const marker = ref(null)
const isLoading = ref(false)
const errorMessage = ref('')

const workFromOptions = [
  { value: 'office', label: 'Office' },
  { value: 'flexi_time', label: 'Flexi Time' },
  { value: 'pkl', label: 'PKL/Magang' }
]

const shiftOptions = [
  { value: 'non_shift', label: 'Non Shift' },
  { value: 'night_shift', label: 'Shift Malam' },
  { value: 'morning_shift', label: 'Shift Pagi' },
  { value: 'afternoon_shift', label: 'Shift Siang' },
  { value: 'pkl', label: 'PKL/Magang' },
  { value: 'overtime', label: 'Lembur' },
  { value: 'flexi_time', label: 'Flexi Time' }
]

const isWithinPTArea = (latitude, longitude) => {
  const R = 6371; // Radius bumi dalam kilometer
  const dLat = (latitude - PT_COORDINATES.latitude) * Math.PI / 180;
  const dLon = (longitude - PT_COORDINATES.longitude) * Math.PI / 180;
  const a =
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(PT_COORDINATES.latitude * Math.PI / 180) *
    Math.cos(latitude * Math.PI / 180) *
    Math.sin(dLon/2) * Math.sin(dLon/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  const distance = R * c; // Jarak dalam kilometer

  console.log('Jarak dari lokasi ke PT Kimia Farma Banjaran:', distance.toFixed(2), 'km');

  return distance <= PT_COORDINATES.radius;
}

const updateDateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
  currentDate.value = now.toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

let timer

const fetchAttendanceData = async () => {
  try {
    const response = await axios.get('/pegawai/api/attendance');
    const { today, monthly, statistics: stats } = response.data;

    console.log('Fetched attendance data:', response.data);

    if (today) {
      attendance.value = {
        status: today.check_out ? 'Sudah Absen' : 'Sudah Check In',
        check_in: today.check_in,
        check_out: today.check_out,
        location: today.latitude && today.longitude ? {
          latitude: today.latitude,
          longitude: today.longitude
        } : null,
        shift: today.shift || 'non_shift'
      };
    } else {
      attendance.value = {
        status: 'Belum Absen',
        check_in: null,
        check_out: null,
        location: null,
        shift: 'non_shift'
      };
    }

    // Update monthly attendance
    monthlyAttendance.value = monthly.map(record => ({
      ...record,
      date: new Date(record.date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }));

    // Update statistics
    statistics.value = {
      total_days: stats.total_days,
      present_days: stats.present_days,
      late_days: stats.late_days,
      leave_days: stats.leave_days,
      percentages: {
        present: Math.round((stats.present_days / stats.total_days) * 100),
        late: Math.round((stats.late_days / stats.total_days) * 100),
        leave: Math.round((stats.leave_days / stats.total_days) * 100)
      }
    };

    console.log('Updated attendance state:', attendance.value);
    console.log('Updated monthly attendance:', monthlyAttendance.value);
    console.log('Updated statistics:', statistics.value);
  } catch (error) {
    console.error('Error fetching attendance data:', error);
    alert('Terjadi kesalahan saat mengambil data absensi. Silakan refresh halaman.');
  }
};

// Tambahkan fungsi untuk refresh data
const refreshData = async () => {
  await fetchAttendanceData()
}

onMounted(async () => {
  updateDateTime()
  timer = setInterval(updateDateTime, 1000)
  
  // Tunggu Google Maps API dimuat
  if (window.google) {
    initMap();
  } else {
    window.addEventListener('google-maps-loaded', initMap);
  }

  // Ambil data absensi
  await fetchAttendanceData()

  // Langsung deteksi lokasi saat halaman dimuat
  try {
    const location = await getLocation();
    console.log('Lokasi didapat:', location);
    
    attendance.value = {
      ...attendance.value,
      location: {
        latitude: location.latitude,
        longitude: location.longitude
      }
    };

    // Update peta dengan posisi baru
    updateUserPosition(location);

    if (!isWithinPTArea(location.latitude, location.longitude)) {
      console.log('Lokasi berada di luar area PT Kimia Farma Banjaran');
      errorMessage.value = 'Anda berada di luar area PT Kimia Farma Banjaran. Mohon absensi di dalam area PT Kimia Farma Banjaran';
    }
  } catch (error) {
    console.error('Error saat mengambil lokasi:', error);
    switch(error.code) {
      case error.PERMISSION_DENIED:
        console.error('Akses lokasi ditolak');
        errorMessage.value = 'Akses lokasi ditolak. Mohon izinkan akses lokasi untuk melanjutkan absensi.';
        break;
      case error.POSITION_UNAVAILABLE:
        console.error('Informasi lokasi tidak tersedia');
        errorMessage.value = 'Informasi lokasi tidak tersedia. Mohon pastikan GPS aktif dan sinyal kuat.';
        break;
      case error.TIMEOUT:
        console.error('Permintaan lokasi timeout');
        errorMessage.value = 'Permintaan lokasi melebihi batas waktu. Mohon coba lagi.';
        break;
      default:
        console.error('Error tidak diketahui:', error);
        errorMessage.value = 'Terjadi kesalahan saat mengambil lokasi. Mohon coba lagi.';
    }
  }
})

onUnmounted(() => {
  clearInterval(timer)
  window.removeEventListener('google-maps-loaded', initMap);
})

const getLocation = () => {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Browser tidak mendukung geolocation'));
      return;
    }

    const options = {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    };

    // Coba dapatkan lokasi dengan high accuracy
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        console.log('Lokasi berhasil didapat:', { latitude, longitude });
        resolve({ latitude, longitude });
      },
      (error) => {
        console.error('Error saat mendapatkan lokasi:', error);
        reject(error);
      },
      options
    );

    // Tambahkan watchPosition untuk update lokasi secara real-time
    navigator.geolocation.watchPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        console.log('Lokasi diperbarui:', { latitude, longitude });
        
        attendance.value = {
          ...attendance.value,
          location: {
            latitude,
            longitude
          }
        };

        // Update peta dengan posisi baru
        updateUserPosition({ latitude, longitude });

        // Cek apakah dalam radius
        if (!isWithinPTArea(latitude, longitude)) {
          errorMessage.value = 'Anda berada di luar area PT Kimia Farma Banjaran. Mohon absensi di dalam area PT Kimia Farma Banjaran';
        } else {
          errorMessage.value = '';
        }
      },
      (error) => {
        console.error('Error saat watch position:', error);
      },
      options
    );
  });
};

const initMap = () => {
  if (!mapRef.value) return;

  // Gunakan lokasi PT sebagai default center
  const center = {
    lat: PT_COORDINATES.latitude,
    lng: PT_COORDINATES.longitude
  };

  map.value = new google.maps.Map(mapRef.value, {
    center: center,
    zoom: 15,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: false,
    zoomControl: true,
    styles: [
      {
        featureType: "poi",
        elementType: "labels",
        stylers: [{ visibility: "off" }]
      }
    ]
  });

  // Tambahkan marker untuk lokasi PT
  new google.maps.Marker({
    position: center,
    map: map.value,
    title: 'PT Kimia Farma Banjaran',
    icon: {
      url: '/images/marker-company.png',
      scaledSize: new google.maps.Size(40, 40)
    }
  });

  // Tambahkan circle untuk radius
  new google.maps.Circle({
    strokeColor: '#FF0000',
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: '#FF0000',
    fillOpacity: 0.35,
    map: map.value,
    center: center,
    radius: PT_COORDINATES.radius * 1000 // Konversi ke meter
  });

  // Jika ada lokasi user, update posisi
  if (attendance.value.location) {
    updateUserPosition(attendance.value.location);
  }
};

const updateUserPosition = (location) => {
  if (!map.value) return;

  const userPosition = {
    lat: location.latitude,
    lng: location.longitude
  };

  // Update center map ke posisi user
  map.value.setCenter(userPosition);

  // Hapus marker lama jika ada
  if (marker.value) {
    marker.value.setMap(null);
  }

  // Tambahkan marker baru
  marker.value = new google.maps.Marker({
    position: userPosition,
    map: map.value,
    title: 'Lokasi Anda',
    icon: {
      url: '/images/marker-user.png',
      scaledSize: new google.maps.Size(40, 40)
    }
  });

  // Tambahkan info window
  const infoWindow = new google.maps.InfoWindow({
    content: `
      <div class="p-2">
        <h3 class="font-bold">Lokasi Anda</h3>
        <p>Latitude: ${location.latitude.toFixed(6)}</p>
        <p>Longitude: ${location.longitude.toFixed(6)}</p>
      </div>
    `
  });

  marker.value.addListener('click', () => {
    infoWindow.open(map.value, marker.value);
  });
};

const handleCheckIn = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    if (!attendance.value.location) {
      errorMessage.value = 'Mohon tunggu hingga lokasi terdeteksi';
      return;
    }

    if (!isWithinPTArea(attendance.value.location.latitude, attendance.value.location.longitude)) {
      errorMessage.value = 'Anda berada di luar area kantor. Silakan mendekat ke area kantor untuk melakukan absensi.';
      return;
    }

    const response = await axios.post('/pegawai/api/attendance/check-in', {
      latitude: attendance.value.location.latitude,
      longitude: attendance.value.location.longitude,
      shift: attendance.value.shift
    });

    if (response.data.success) {
      // Update data absensi hari ini
      attendance.value = {
        ...attendance.value,
        status: response.data.data.status,
        check_in: response.data.data.check_in
      };

      // Refresh data statistik dan riwayat
      await fetchAttendanceData();
    
      alert('Absensi masuk berhasil');
    } else {
      errorMessage.value = response.data.message || 'Gagal melakukan absensi masuk';
    }
  } catch (error) {
    console.error('Error saat check in:', error);
    if (error.response) {
      errorMessage.value = error.response.data.message || 'Terjadi kesalahan saat melakukan absensi masuk';
    } else {
      errorMessage.value = 'Terjadi kesalahan saat melakukan absensi masuk. Silakan coba lagi.';
    }
  } finally {
    isLoading.value = false;
  }
};

const checkOut = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    if (!attendance.value.location) {
      errorMessage.value = 'Mohon tunggu hingga lokasi terdeteksi';
      return;
    }

    if (!isWithinPTArea(attendance.value.location.latitude, attendance.value.location.longitude)) {
      errorMessage.value = 'Anda berada di luar area kantor. Silakan mendekat ke area kantor untuk melakukan absensi.';
      return;
    }

    const response = await axios.post('/pegawai/api/attendance/check-out', {
      latitude: attendance.value.location.latitude,
      longitude: attendance.value.location.longitude
    });

    if (response.data.success) {
      // Update data absensi hari ini
      attendance.value = {
        ...attendance.value,
        status: response.data.data.status,
        check_out: response.data.data.check_out
      };

      // Update monthly attendance dan statistics
      if (response.data.monthly) {
        monthlyAttendance.value = response.data.monthly.map(record => ({
          ...record,
          date: new Date(record.date).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          })
        }));
      }

      if (response.data.statistics) {
        statistics.value = {
          ...response.data.statistics,
          percentages: {
            present: Math.round((response.data.statistics.present_days / response.data.statistics.total_days) * 100),
            late: Math.round((response.data.statistics.late_days / response.data.statistics.total_days) * 100),
            leave: Math.round((response.data.statistics.leave_days / response.data.statistics.total_days) * 100)
          }
        };
      }
    
      alert('Absensi pulang berhasil');
    } else {
      errorMessage.value = response.data.message || 'Gagal melakukan absensi pulang';
    }
  } catch (error) {
    console.error('Error saat check out:', error);
    if (error.response) {
      errorMessage.value = error.response.data.message || 'Terjadi kesalahan saat melakukan absensi pulang';
    } else {
      errorMessage.value = 'Terjadi kesalahan saat melakukan absensi pulang. Silakan coba lagi.';
    }
  } finally {
    isLoading.value = false;
  }
};

// Fungsi untuk format waktu
const formatTime = (time) => {
  if (!time) return '-';
  const [hours, minutes] = time.split(':');
  return `${hours}:${minutes}`;
};
</script>

<template>
  <Head title="Absensi Pegawai" />

  <AuthenticatedLayout>
    <template #header>
      <!-- Breadcrumbs -->
      <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex">
          <li class="flex items-center">
            <i class="fas fa-home text-blue-600 mr-1"></i>
            <a href="/pegawai/dashboard" class="text-blue-600 hover:text-blue-800 font-semibold">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
          </li>
          <li class="flex items-center text-gray-700 font-semibold">
            Absensi
          </li>
        </ol>
      </nav>
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Absensi</h1>
        <div class="text-right">
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ errorMessage }}
        </div>

        <!-- Status Absensi Hari Ini -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
          <h2 class="text-lg font-semibold mb-4">Status Absensi Hari Ini</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Shift</label>
                <select v-model="attendance.shift" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option v-for="shift in shiftOptions" :key="shift.value" :value="shift.value">
                    {{ shift.label }}
                  </option>
                </select>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <div class="mt-1">
                  <span class="px-2 py-1 rounded text-sm" :class="{
                    'bg-green-100 text-green-800': attendance.status === 'Hadir',
                    'bg-yellow-100 text-yellow-800': attendance.status === 'Terlambat',
                    'bg-red-100 text-red-800': attendance.status === 'Cuti',
                    'bg-blue-100 text-blue-800': attendance.status === 'Sudah Absen'
                  }">
                    {{ attendance.status || 'Belum Absen' }}
                  </span>
                </div>
              </div>
            </div>
            <div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Check In</label>
                <div class="mt-1">
                  <span class="text-sm text-gray-600">{{ attendance.check_in ? formatTime(attendance.check_in) : '-' }}</span>
                </div>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Check Out</label>
                <div class="mt-1">
                  <span class="text-sm text-gray-600">{{ attendance.check_out ? formatTime(attendance.check_out) : '-' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Lokasi -->
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700">Lokasi Saat Ini</label>
            <div class="mt-1 p-3 bg-gray-50 rounded-lg">
              <div v-if="attendance.location" class="space-y-2">
                <div class="text-sm text-gray-600">
                  <span class="font-medium">Latitude:</span> {{ attendance.location.latitude?.toFixed(6) }}
                </div>
                <div class="text-sm text-gray-600">
                  <span class="font-medium">Longitude:</span> {{ attendance.location.longitude?.toFixed(6) }}
                </div>
                <div ref="mapRef" class="w-full h-64 rounded-lg border-2 border-gray-300"></div>
              </div>
              <div v-else class="text-yellow-600 text-sm">
                Mendeteksi lokasi...
              </div>
            </div>
          </div>

          <div class="mt-4 flex space-x-4">
            <button 
              @click="handleCheckIn" 
              :disabled="attendance.status === 'Sudah Absen' || isLoading"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 flex items-center"
            >
              <span v-if="isLoading" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              Check In
            </button>
            <button 
              @click="checkOut" 
              :disabled="!attendance.check_in || attendance.check_out || isLoading"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 flex items-center"
            >
              <span v-if="isLoading" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              Check Out
            </button>
          </div>
        </div>

        <!-- Statistik Absensi -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
          <h2 class="text-lg font-semibold mb-4">Statistik Absensi Bulan Ini</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
              <div class="relative w-24 h-24 mx-auto">
                <svg class="w-full h-full" viewBox="0 0 36 36">
                  <path
                    d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#e2e8f0"
                    stroke-width="3"
                  />
                  <path
                    d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#10b981"
                    stroke-width="3"
                    :stroke-dasharray="`${statistics.percentages.present}, 100`"
                  />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-lg font-bold text-green-600">{{ statistics.percentages.present }}%</span>
                </div>
              </div>
              <div class="mt-2 text-sm text-gray-600">Hadir</div>
            </div>

            <div class="text-center">
              <div class="relative w-24 h-24 mx-auto">
                <svg class="w-full h-full" viewBox="0 0 36 36">
                  <path
                    d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#e2e8f0"
                    stroke-width="3"
                  />
                  <path
                    d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#eab308"
                    stroke-width="3"
                    :stroke-dasharray="`${statistics.percentages.late}, 100`"
                  />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-lg font-bold text-yellow-600">{{ statistics.percentages.late }}%</span>
                </div>
              </div>
              <div class="mt-2 text-sm text-gray-600">Terlambat</div>
            </div>

            <div class="text-center">
              <div class="relative w-24 h-24 mx-auto">
                <svg class="w-full h-full" viewBox="0 0 36 36">
                  <path
                    d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#e2e8f0"
                    stroke-width="3"
                  />
                  <path
                    d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#ef4444"
                    stroke-width="3"
                    :stroke-dasharray="`${statistics.percentages.leave}, 100`"
                  />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-lg font-bold text-red-600">{{ statistics.percentages.leave }}%</span>
                </div>
              </div>
              <div class="mt-2 text-sm text-gray-600">Cuti</div>
            </div>
          </div>
        </div>

        <!-- Riwayat Absensi Bulanan -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-semibold mb-4">Riwayat Absensi Bulanan</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-2">Tanggal</th>
                  <th class="text-left py-2">Shift</th>
                  <th class="text-left py-2">Check In</th>
                  <th class="text-left py-2">Check Out</th>
                  <th class="text-left py-2">Status</th>
                  <th class="text-left py-2">Lokasi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="record in monthlyAttendance" :key="record.date" class="border-b">
                  <td class="py-2">{{ record.date }}</td>
                  <td class="py-2">
                    <span class="px-2 py-1 rounded text-sm bg-blue-100 text-blue-800">
                      {{ record.shift ? shiftOptions.find(s => s.value === record.shift)?.label || record.shift : '-' }}
                    </span>
                  </td>
                  <td class="py-2">
                    <span v-if="record.check_in" class="text-sm text-gray-600">
                      {{ formatTime(record.check_in) }}
                    </span>
                    <span v-else class="text-sm text-gray-400">-</span>
                  </td>
                  <td class="py-2">
                    <span v-if="record.check_out" class="text-sm text-gray-600">
                      {{ formatTime(record.check_out) }}
                    </span>
                    <span v-else class="text-sm text-gray-400">-</span>
                  </td>
                  <td class="py-2">
                    <span class="px-2 py-1 rounded text-sm" :class="{
                      'bg-green-100 text-green-800': record.status === 'Hadir',
                      'bg-yellow-100 text-yellow-800': record.status === 'Terlambat',
                      'bg-red-100 text-red-800': record.status === 'Cuti',
                      'bg-blue-100 text-blue-800': record.status === 'Sudah Absen'
                    }">
                      {{ record.status }}
                    </span>
                  </td>
                  <td class="py-2">
                    <span v-if="record.latitude && record.longitude" class="text-sm text-gray-600">
                      {{ record.latitude.toFixed(6) }}, {{ record.longitude.toFixed(6) }}
                    </span>
                    <span v-else class="text-sm text-gray-400">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>