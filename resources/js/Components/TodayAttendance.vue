<template>
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-lg font-semibold mb-4">Status Absensi Hari Ini</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Shift</label>
          <select 
            v-model="attendance.shift" 
            :disabled="attendance.status === 'Sudah Absen'"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
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
            <span v-if="attendance.check_in" class="text-sm text-gray-600">
              {{ formatTime(attendance.check_in) }}
              <span class="text-xs text-gray-400">WIB</span>
            </span>
            <span v-else class="text-sm text-gray-400">-</span>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Check Out</label>
          <div class="mt-1">
            <span v-if="attendance.check_out" class="text-sm text-gray-600">
              {{ formatTime(attendance.check_out) }}
              <span class="text-xs text-gray-400">WIB</span>
            </span>
            <span v-else class="text-sm text-gray-400">-</span>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Waktu Saat Ini</label>
          <div class="mt-1">
            <span class="text-sm text-gray-600">
              {{ currentTime }}
              <span class="text-xs text-gray-400">WIB</span>
            </span>
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
        :disabled="attendance.status === 'Sudah Absen' || isLoading || !isWithinRadius"
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
        :disabled="!attendance.check_in || attendance.check_out || isLoading || !isWithinRadius"
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
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.locale('id');

// Set default timezone ke Asia/Jakarta
dayjs.tz.setDefault('Asia/Jakarta');

const props = defineProps({
  attendance: {
    type: Object,
    required: true
  },
  shiftOptions: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['update:attendance', 'check-in', 'check-out']);

const mapRef = ref(null);
const map = ref(null);
const marker = ref(null);
const isLoading = ref(false);
const errorMessage = ref('');
const currentTime = ref('');
const currentDate = ref('');

const updateDateTime = () => {
  const now = dayjs().tz('Asia/Jakarta');
  currentTime.value = now.format('HH:mm:ss');
  currentDate.value = now.format('dddd, D MMMM YYYY');
};

let timer;

// Koordinat PT Kimia Farma Tbk Plant Banjaran
const PT_COORDINATES = {
  latitude: -7.039,  // 7°02'20.4"S
  longitude: 107.596, // 107°35'45.8"E
  radius: 2 // radius dalam kilometer
};

const isWithinRadius = computed(() => {
  if (!props.attendance.location) return false;
  return isWithinPTArea(props.attendance.location.latitude, props.attendance.location.longitude);
});

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
};

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
        
        emit('update:attendance', {
          ...props.attendance,
          location: {
            latitude,
            longitude
          }
        });

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
  if (props.attendance.location) {
    updateUserPosition(props.attendance.location);
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

    if (!props.attendance.location) {
      errorMessage.value = 'Mohon tunggu hingga lokasi terdeteksi';
      return;
    }

    if (!isWithinPTArea(props.attendance.location.latitude, props.attendance.location.longitude)) {
      errorMessage.value = 'Anda berada di luar area kantor. Silakan mendekat ke area kantor untuk melakukan absensi.';
      return;
    }

    emit('check-in', {
      latitude: props.attendance.location.latitude,
      longitude: props.attendance.location.longitude,
      shift: props.attendance.shift
    });
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

    if (!props.attendance.location) {
      errorMessage.value = 'Mohon tunggu hingga lokasi terdeteksi';
      return;
    }

    if (!isWithinPTArea(props.attendance.location.latitude, props.attendance.location.longitude)) {
      errorMessage.value = 'Anda berada di luar area kantor. Silakan mendekat ke area kantor untuk melakukan absensi.';
      return;
    }

    const response = await axios.post('/pegawai/api/attendance/check-out', {
      latitude: props.attendance.location.latitude,
      longitude: props.attendance.location.longitude
    });

    if (response.data.success) {
      // Update data absensi hari ini
      emit('update:attendance', {
        ...props.attendance,
        status: response.data.data.status,
        check_out: response.data.data.check_out
      });

      // Refresh data statistik dan riwayat
      if (response.data.monthly) {
        emit('update:monthly', response.data.monthly);
      }

      if (response.data.statistics) {
        emit('update:statistics', response.data.statistics);
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

const formatTime = (time) => {
  if (!time) return '-';
  // Pastikan waktu dalam format yang benar dan konversi ke WIB
  const jakartaTime = dayjs(time).tz('Asia/Jakarta');
  return jakartaTime.format('HH:mm:ss');
};

onMounted(() => {
  updateDateTime();
  timer = setInterval(updateDateTime, 1000);

  // Tunggu Google Maps API dimuat
  if (window.google) {
    initMap();
  } else {
    window.addEventListener('google-maps-loaded', initMap);
  }

  // Langsung deteksi lokasi saat halaman dimuat
  try {
    const location = await getLocation();
    console.log('Lokasi didapat:', location);
    
    emit('update:attendance', {
      ...props.attendance,
      location: {
        latitude: location.latitude,
        longitude: location.longitude
      }
    });

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
});

onUnmounted(() => {
  clearInterval(timer);
  window.removeEventListener('google-maps-loaded', initMap);
});
</script> 