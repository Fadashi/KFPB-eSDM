<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
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
const successMessage = ref('')

// Tambahkan state untuk debug response
const responseCheckIn = ref(null)
const responseAttendanceData = ref(null)

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
    responseAttendanceData.value = response.data;
    const { today, monthly, statistics: stats } = response.data;
    console.log('Fetched attendance data:', response.data);

    if (today) {
      attendance.value = {
        status: today.check_out ? 'Sudah Absen' : (today.status || 'Sudah Check In'),
        check_in: today.check_in,
        check_out: today.check_out,
        location: today.latitude && today.longitude ? {
          latitude: today.latitude,
          longitude: today.longitude
        } : null,
        shift: today.shift || 'non_shift'
      };
      console.log('Updated attendance state:', attendance.value);
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

    console.log('Updated monthly attendance:', monthlyAttendance.value);
    console.log('Updated statistics:', statistics.value);
  } catch (error) {
    console.error('Error fetching attendance data:', error);
    errorMessage.value = 'Terjadi kesalahan saat mengambil data absensi. Silakan refresh halaman.';
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
    responseCheckIn.value = response.data;
    console.log('Response check-in:', response.data);

    if (response.data.success) {
      await fetchAttendanceData();
      successMessage.value = 'Absensi masuk berhasil!';
      setTimeout(() => successMessage.value = '', 3000);
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

// Tambahkan state dan computed untuk NPP, workFrom, dan validasi form
// Misal NPP diambil dari user login, contoh:
const npp = '20000427R' // Ganti dengan logic dari user login jika ada
const workFrom = ref('')
const formValid = computed(() => {
  return npp && workFrom.value && attendance.value.shift && attendance.value.location && isWithinPTArea(attendance.value.location.latitude, attendance.value.location.longitude)
})

// Tambahkan computed onlyDate untuk format tanggal tanpa hari
const onlyDate = computed(() => {
  // currentDate misal: "Kamis, 8 Mei 2025"
  // Ambil bagian tanggal saja: "8 Mei 2025"
  if (!currentDate.value) return '';
  const parts = currentDate.value.split(', ');
  return parts.length > 1 ? parts[1] : currentDate.value;
})
</script>

<template>
  <Head title="Absensi Pegawai" />

  <AuthenticatedLayout>
    <template #header>
      <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex">
          <li class="flex items-center">
            <i class="fas fa-home text-blue-600 mr-1"></i>
            <a href="/pegawai/dashboard" class="text-blue-600 hover:text-blue-800 font-semibold">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a 1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
          </li>
          <li class="flex items-center text-gray-700 font-semibold">
            Absensi
          </li>
        </ol>
      </nav>
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Absensi</h1>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Jam dan Tanggal Besar di Atas Form -->
        <div class="text-center mb-2">
          <div class="text-5xl font-extrabold" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; letter-spacing: 0.1em; text-shadow: 0 2px 8px rgba(0,0,0,0.10); color: #222;">
            {{ currentTime }}
          </div>
          <div class="text-xl font-bold mt-1" style="font-family: 'Montserrat', 'Poppins', 'Inter', 'Segoe UI', Arial, sans-serif; color: #444;">
            {{ onlyDate }}
          </div>
        </div>
        <!-- Tambahkan keterangan di atas jam real-time -->
        <div class="text-center mb-1">
          <span class="inline-flex items-center text-sm font-semibold" style="color: #eab308">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#eab308" stroke-width="2" fill="none"/><path stroke="#eab308" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
            Check in di atas jam 07.00 dianggap Terlambat
          </span>
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ errorMessage }}
        </div>
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded text-center font-bold">
          {{ successMessage }}
        </div>

        <!-- Form Absen Masuk -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
          <h2 class="text-lg font-semibold mb-4">Absen Masuk</h2>
          <form @submit.prevent="handleCheckIn">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">NPP</label>
              <input type="text" :value="npp" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Work From</label>
              <select v-model="workFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">-- Pilih Work From --</option>
                <option value="office">Work From Office (WFO)</option>
                <option value="flexi_time">Flexi Time</option>
                <option value="pkl">PKL</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Shift</label>
              <select v-model="attendance.shift" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">-- Pilih Shift--</option>
                <optgroup label="Banjaran">
                  <option value="non_shift">Non Shift BNJ (07:0-15:30)</option>
                  <option value="night_shift">Panjang (Malam) BNJ (19:3-04:00)</option>
                  <option value="short_night">Pendek (Malam) BNJ (20:3-05:00)</option>
                  <option value="ipal_pagi">IPAL Pagi BNJ (05:3-14:00)</option>
                  <option value="ipal_siang">IPAL Siang BNJ (13:3-22:00)</option>
                  <option value="pkl">PKL/Magang (00:0-00:0)</option>
                  <option value="overtime">Lembur (libur) BNJ (00:0-00:00)</option>
                  <option value="flexi_time">Flexi Time (00:0-00:00)</option>
                  <option value="night2">Panjang2 (Malam) BNJ (19:0-03:30)</option>
                  <option value="afternoon_shift">Shift Siang BNJ (14:0-22:10)</option>
                  <option value="shift_malam">Shift Malam BNJ (22:0-06:1)</option>
                  <option value="morning_shift">Shift Pagi BNJ (06:0-14:1)</option>
                </optgroup>
              </select>
              <div v-if="attendance.shift === 'overtime'" class="text-green-700 font-semibold text-sm mt-1">
                Jika lembur sabtu/minggu/libur, Pilih shift lembur (libur)!
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Lokasi</label>
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
            <div class="mb-4">
              <span class="inline-block px-3 py-1 rounded-full text-sm font-bold"
                :class="{
                  'bg-gray-200 text-gray-700': attendance.status === 'Belum Absen' || !attendance.status,
                  'bg-green-100 text-green-800': attendance.status === 'Hadir',
                  'bg-yellow-100 text-yellow-800': attendance.status === 'Terlambat',
                  'bg-red-100 text-red-800': attendance.status === 'Cuti',
                  'bg-blue-100 text-blue-800': attendance.status === 'Sudah Absen'
                }"
              >
                {{ attendance.status || 'Belum Absen' }}
              </span>
            </div>
            <div class="mt-6 flex gap-4">
              <button
                type="button"
                @click="handleCheckIn"
                :disabled="attendance.check_in || !formValid || isLoading"
                class="w-1/2 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded disabled:opacity-60 text-lg"
              >
                Check In
              </button>
              <button
                type="button"
                @click="checkOut"
                :disabled="!attendance.check_in || attendance.check_out || !formValid || isLoading"
                class="w-1/2 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded disabled:opacity-60 text-lg"
              >
                Check Out
              </button>
            </div>
          </form>
        </div>

        <!-- Statistik Absensi Bulan Ini dengan diagram bulat -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
          <h2 class="text-lg font-semibold mb-4">Statistik Absensi Bulan Ini</h2>
          <div class="flex justify-center items-center">
            <svg width="160" height="160" viewBox="0 0 42 42" class="mr-8">
              <!-- Background -->
              <circle r="15.9155" cx="21" cy="21" fill="#f3f4f6" />
              <!-- Hadir -->
              <circle
                r="15.9155" cx="21" cy="21"
                fill="transparent"
                stroke="#10b981"
                stroke-width="4"
                stroke-dasharray="{{ statistics.percentages.present }}, 100"
                stroke-dashoffset="25"
              />
              <!-- Terlambat -->
              <circle
                r="15.9155" cx="21" cy="21"
                fill="transparent"
                stroke="#eab308"
                stroke-width="4"
                stroke-dasharray="{{ statistics.percentages.late }}, 100"
                stroke-dashoffset="{{ 25 + statistics.percentages.present }}"
              />
              <!-- Cuti -->
              <circle
                r="15.9155" cx="21" cy="21"
                fill="transparent"
                stroke="#ef4444"
                stroke-width="4"
                stroke-dasharray="{{ statistics.percentages.leave }}, 100"
                stroke-dashoffset="{{ 25 + statistics.percentages.present + statistics.percentages.late }}"
              />
              <text x="21" y="23" text-anchor="middle" font-size="8" font-weight="bold" fill="#222">{{ statistics.percentages.present }}%</text>
            </svg>
            <div>
              <div class="flex items-center mb-2">
                <span class="inline-block w-4 h-4 rounded-full mr-2" style="background:#10b981"></span> Hadir: <span class="ml-2 font-bold">{{ statistics.present_days }}</span>
              </div>
              <div class="flex items-center mb-2">
                <span class="inline-block w-4 h-4 rounded-full mr-2" style="background:#eab308"></span> Terlambat: <span class="ml-2 font-bold">{{ statistics.late_days }}</span>
              </div>
              <div class="flex items-center">
                <span class="inline-block w-4 h-4 rounded-full mr-2" style="background:#ef4444"></span> Cuti: <span class="ml-2 font-bold">{{ statistics.leave_days }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Riwayat Absensi Bulanan dalam bentuk tabel -->
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