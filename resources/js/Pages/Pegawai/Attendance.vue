<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import axios from 'axios'

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

// Koordinat PT Kimia Farma Banjaran
const PT_COORDINATES = {
  latitude: -7.039,  // 7°02'20.4"S
  longitude: 107.596, // 107°35'45.8"E
  radius: 2 // radius dalam kilometer
}

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
    const response = await axios.get('/pegawai/api/attendance')
    const { today, monthly, statistics: stats } = response.data

    console.log('Fetched attendance data:', response.data)

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
      }
    } else {
      attendance.value = {
        status: 'Belum Absen',
        check_in: null,
        check_out: null,
        location: null,
        shift: 'non_shift'
      }
    }

    monthlyAttendance.value = monthly
    statistics.value = {
      ...stats,
      percentages: {
        present: Math.round((stats.present_days / stats.total_days) * 100),
        late: Math.round((stats.late_days / stats.total_days) * 100),
        leave: Math.round((stats.leave_days / stats.total_days) * 100)
      }
    }

    console.log('Updated attendance state:', attendance.value)
    console.log('Updated monthly attendance:', monthlyAttendance.value)
    console.log('Updated statistics:', statistics.value)
  } catch (error) {
    console.error('Error fetching attendance data:', error)
  }
}

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

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        resolve({ latitude, longitude });
      },
      (error) => {
        reject(error);
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
        <p>Latitude: {{ location.latitude.toFixed(6) }}</p>
        <p>Longitude: {{ location.longitude.toFixed(6) }}</p>
      </div>
    `
  });

  marker.value.addListener('click', () => {
    infoWindow.open(map.value, marker.value);
  });
};

const watchLocation = async () => {
  try {
    // Hentikan deteksi lokasi jika sudah ada lokasi
    if (attendance.value.location) {
      console.log('Lokasi sudah ada, tidak perlu mendeteksi ulang');
      return;
    }

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

    if (isWithinPTArea(location.latitude, location.longitude)) {
      console.log('Lokasi berada dalam area PT Kimia Farma Banjaran');
    } else {
      console.log('Lokasi berada di luar area PT Kimia Farma Banjaran');
      alert('Anda berada di luar area PT Kimia Farma Banjaran. Mohon absensi di dalam area PT Kimia Farma Banjaran');
    }
  } catch (error) {
    console.error('Error saat mengambil lokasi:', error);
    switch(error.code) {
      case error.PERMISSION_DENIED:
        console.error('Akses lokasi ditolak');
        alert('Akses lokasi ditolak. Mohon izinkan akses lokasi untuk melanjutkan absensi.');
        break;
      case error.POSITION_UNAVAILABLE:
        console.error('Informasi lokasi tidak tersedia');
        alert('Informasi lokasi tidak tersedia. Mohon pastikan GPS aktif dan sinyal kuat.');
        break;
      case error.TIMEOUT:
        console.error('Permintaan lokasi timeout');
        alert('Permintaan lokasi melebihi batas waktu. Mohon coba lagi.');
        break;
      default:
        console.error('Error tidak diketahui:', error);
        alert('Terjadi kesalahan saat mengambil lokasi. Mohon coba lagi.');
    }
  }
};

const calculatePercentages = () => {
  statistics.value.percentages = {
    present: Math.round((statistics.value.present_days / statistics.value.total_days) * 100),
    late: Math.round((statistics.value.late_days / statistics.value.total_days) * 100),
    leave: Math.round((statistics.value.leave_days / statistics.value.total_days) * 100)
  }
}

const checkIn = async () => {
  try {
    // Ambil lokasi terbaru
    await watchLocation();
    
    if (!attendance.value.location) {
      alert('Mohon izinkan akses lokasi terlebih dahulu');
      return;
    }

    console.log('Sending check in request with location:', attendance.value.location);

    const response = await axios.post('/pegawai/api/attendance/check-in', {
      latitude: attendance.value.location.latitude,
      longitude: attendance.value.location.longitude,
      work_from: attendance.value.work_from,
      shift: attendance.value.shift
    });

    console.log('Check in response:', response.data);

    // Update data lokal dengan response dari server
    attendance.value = {
      ...attendance.value,
      status: response.data.data.status,
      check_in: response.data.data.check_in,
      location: {
        latitude: response.data.data.latitude,
        longitude: response.data.data.longitude
      },
      work_from: response.data.data.work_from,
      shift: response.data.data.shift
    };

    // Update statistik dan riwayat absensi
    statistics.value = response.data.statistics;
    monthlyAttendance.value = response.data.monthly;

    // Update persentase
    calculatePercentages();

    // Refresh data setelah check in
    await refreshData()
    
    alert('Absensi masuk berhasil');
  } catch (error) {
    console.error('Error checking in:', error);
    console.error('Error response:', error.response?.data);
    alert(error.response?.data?.message || 'Terjadi kesalahan saat absensi masuk');
  }
}

const checkOut = async () => {
  try {
    // Ambil lokasi terbaru
    await watchLocation();
    
    if (!attendance.value.location) {
      alert('Mohon izinkan akses lokasi terlebih dahulu');
      return;
    }

    console.log('Sending check out request with location:', attendance.value.location);

    const response = await axios.post('/pegawai/api/attendance/check-out', {
      latitude: attendance.value.location.latitude,
      longitude: attendance.value.location.longitude
    });

    console.log('Check out response:', response.data);

    // Update data lokal dengan response dari server
    attendance.value = {
      ...attendance.value,
      status: 'Sudah Absen',
      check_out: response.data.data.check_out,
      location: {
        latitude: response.data.data.latitude,
        longitude: response.data.data.longitude
      }
    };

    // Update statistik dan riwayat absensi
    statistics.value = response.data.statistics;
    monthlyAttendance.value = response.data.monthly;

    // Update persentase
    calculatePercentages();

    // Refresh data setelah check out
    await refreshData()
    
    alert('Absensi pulang berhasil');
  } catch (error) {
    console.error('Error checking out:', error);
    console.error('Error response:', error.response?.data);
    alert(error.response?.data?.message || 'Terjadi kesalahan saat absensi pulang');
  }
}

// Mendapatkan riwayat absensi
const getAttendances = async (month = null, year = null) => {
  const response = await axios.get('/api/attendance', {
    params: { month, year }
  });
  return response.data;
};

// Mendapatkan statistik
const getStatistics = async (month = null, year = null) => {
  const response = await axios.get('/api/attendance/statistics', {
    params: { month, year }
  });
  return response.data;
};

// Menambah absensi
const addAttendance = async (data) => {
  const response = await axios.post('/api/attendance', data);
  return response.data;
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

    <div class="p-6 space-y-6">
      <!-- Absensi Hari Ini -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Bagian Absensi -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Absensi Hari Ini</h2>

          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <span class="text-gray-600">Status:</span>
              <span :class="{
                'text-green-500': attendance.status === 'Hadir',
                'text-yellow-500': attendance.status === 'Terlambat',
                'text-red-500': attendance.status === 'Cuti'
              }" class="font-semibold">
                {{ attendance.status }}
              </span>
            </div>

            <div class="flex justify-between items-center mb-2">
              <span class="text-gray-600">Check In:</span>
              <span class="font-semibold">{{ attendance.check_in || '-' }}</span>
            </div>

            <div class="flex justify-between items-center mb-4">
              <span class="text-gray-600">Check Out:</span>
              <span class="font-semibold">{{ attendance.check_out || '-' }}</span>
            </div>

            <!-- Jam Real-time -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
              <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 mb-1">{{ currentTime }}</div>
                <div class="text-sm text-gray-600">{{ currentDate }}</div>
              </div>
            </div>

            <!-- NPP dan Shift -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
              <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">NPP:</span>
                <span class="font-semibold">{{ $page.props.auth.user.npp }}</span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-gray-600">Shift:</span>
                <select 
                  v-model="attendance.shift"
                  :disabled="attendance.check_in"
                  class="border rounded px-2 py-1"
                >
                  <option v-for="option in shiftOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Lokasi -->
            <div class="mb-4">
              <div class="p-3 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-600">
                  <div class="font-semibold mb-1">Lokasi Saat Ini:</div>
                  <div v-if="attendance.location">
                    <div>Latitude: {{ attendance.location.latitude?.toFixed(6) }}</div>
                    <div>Longitude: {{ attendance.location.longitude?.toFixed(6) }}</div>
                    <div class="mt-2">
                      <div ref="mapRef" class="w-full h-64 rounded-lg border-2 border-gray-300"></div>
                    </div>
                  </div>
                  <div v-else class="text-yellow-600">
                    Menunggu deteksi lokasi...
                    <button 
                      @click="watchLocation" 
                      class="ml-2 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                      Deteksi Lokasi
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex space-x-4">
              <button
                @click="checkIn"
                :disabled="!attendance.location || attendance.check_in"
                class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50"
              >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Check In
              </button>

              <button
                @click="checkOut"
                :disabled="!attendance.location || !attendance.check_in || attendance.check_out"
                class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50"
              >
                <i class="fas fa-sign-out-alt mr-2"></i>
                Check Out
              </button>
            </div>
          </div>
        </div>

        <!-- Bagian Statistik -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Statistik Bulan Ini</h2>

          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ statistics.total_days }}</div>
              <div class="text-gray-600">Total Hari</div>
            </div>

            <div class="bg-green-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ statistics.present_days }}</div>
              <div class="text-gray-600">Hadir</div>
            </div>

            <div class="bg-yellow-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-yellow-600">{{ statistics.late_days }}</div>
              <div class="text-gray-600">Terlambat</div>
            </div>

            <div class="bg-red-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-red-600">{{ statistics.leave_days }}</div>
              <div class="text-gray-600">Cuti</div>
            </div>
          </div>

          <!-- Diagram Persentase -->
          <div class="grid grid-cols-3 gap-4">
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
      </div>

      <!-- Riwayat Absensi Bulanan -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Riwayat Absensi Bulanan</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b">
                <th class="text-left py-2">Tanggal</th>
                <th class="text-left py-2">Check In</th>
                <th class="text-left py-2">Check Out</th>
                <th class="text-left py-2">Status</th>
                <th class="text-left py-2">Work From</th>
                <th class="text-left py-2">Shift</th>
                <th class="text-left py-2">Lokasi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="record in monthlyAttendance" :key="record.date" class="border-b">
                <td class="py-2">{{ record.date }}</td>
                <td class="py-2">{{ record.check_in || '-' }}</td>
                <td class="py-2">{{ record.check_out || '-' }}</td>
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
                  <span class="text-sm text-gray-600">
                    {{ record.work_from || '-' }}
                  </span>
                </td>
                <td class="py-2">
                  <span class="text-sm text-gray-600">
                    {{ record.shift || '-' }}
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
  </AuthenticatedLayout>
</template>

<style scoped>
/* Hapus definisi CSS manual dan gunakan kelas Tailwind langsung di template */
</style>
