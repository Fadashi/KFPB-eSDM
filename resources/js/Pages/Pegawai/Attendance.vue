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
  photo: null
})
const showCamera = ref(false)
const videoRef = ref(null)
const stream = ref(null)
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

onMounted(async () => {
  updateDateTime()
  timer = setInterval(updateDateTime, 1000)
  await fetchAttendanceData()
  watchLocation() // Mulai memantau lokasi secara otomatis
})

onUnmounted(() => {
  clearInterval(timer)
  stopCamera()
})

const fetchAttendanceData = async () => {
  try {
    const response = await axios.get('/pegawai/api/attendance')
    const { today, monthly, statistics: stats } = response.data

    if (today) {
      attendance.value = {
        status: today.check_out ? 'Sudah Absen' : 'Sudah Check In',
        check_in: today.check_in,
        check_out: today.check_out,
        location: today.latitude && today.longitude ? {
          latitude: today.latitude,
          longitude: today.longitude
        } : null,
        photo: today.photo
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
  } catch (error) {
    console.error('Error fetching attendance data:', error)
  }
}

const watchLocation = () => {
  console.log('Memulai pemantauan lokasi...');
  
  if (!navigator.geolocation) {
    console.error('Browser tidak mendukung geolocation');
    alert('Browser Anda tidak mendukung fitur geolocation. Mohon gunakan browser yang lebih modern.');
    return;
  }

  const options = {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 0
  };

  // Mulai memantau lokasi
  navigator.geolocation.watchPosition(
    (position) => {
      console.log('Lokasi terbaru didapat:', position);
      const { latitude, longitude } = position.coords;
      
      console.log('Koordinat terbaru:', { latitude, longitude });
      
      // Simpan lokasi
      attendance.value = {
        ...attendance.value,
        location: {
          latitude,
          longitude
        }
      };
      
      console.log('Lokasi berhasil disimpan:', attendance.value.location);
      
      // Cek apakah lokasi berada dalam area PT
      if (isWithinPTArea(latitude, longitude)) {
        console.log('Lokasi berada dalam area PT Kimia Farma Banjaran');
      } else {
        console.log('Lokasi berada di luar area PT Kimia Farma Banjaran');
        alert('Anda berada di luar area PT Kimia Farma Banjaran. Mohon absensi di dalam area PT Kimia Farma Banjaran');
      }
    },
    (error) => {
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
    },
    options
  );
}

const startCamera = async () => {
  try {
    if (stream.value) {
      stream.value.getTracks().forEach(track => track.stop());
      stream.value = null;
    }

    const constraints = {
      video: {
        facingMode: 'user',
        width: { ideal: 480 },
        height: { ideal: 360 }
      }
    };

    console.log('Meminta akses kamera...');
    stream.value = await navigator.mediaDevices.getUserMedia(constraints);
    console.log('Akses kamera berhasil');

    await nextTick();

    if (videoRef.value) {
      console.log('Mengatur video element...');
      videoRef.value.srcObject = stream.value;
      videoRef.value.onloadedmetadata = () => {
        videoRef.value.play();
        console.log('Video element siap');
      };
    } else {
      console.error('Video element tidak ditemukan');
      alert('Tidak dapat mengakses kamera. Mohon refresh halaman dan coba lagi.');
    }
    
    showCamera.value = true;
  } catch (err) {
    console.error('Error mengakses kamera:', err);
    if (err.name === 'NotAllowedError') {
      alert('Akses kamera ditolak. Mohon izinkan akses kamera untuk melanjutkan absensi.');
    } else if (err.name === 'NotFoundError') {
      alert('Kamera tidak ditemukan. Mohon pastikan kamera terhubung dan berfungsi dengan baik.');
    } else {
      alert('Tidak dapat mengakses kamera. Mohon pastikan kamera berfungsi dengan baik.');
    }
  }
}

const stopCamera = () => {
  if (stream.value) {
    stream.value.getTracks().forEach(track => track.stop());
    stream.value = null;
  }
  showCamera.value = false;
}

const capturePhoto = () => {
  if (!videoRef.value) {
    console.error('Video element tidak ditemukan');
    alert('Tidak dapat mengambil foto. Mohon refresh halaman dan coba lagi.');
    return;
  }

  try {
    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    const ctx = canvas.getContext('2d');
    
    ctx.drawImage(videoRef.value, 0, 0, canvas.width, canvas.height);
    
    const photoData = canvas.toDataURL('image/jpeg', 0.8);
    console.log('Foto berhasil diambil, ukuran:', photoData.length, 'bytes');
    
    attendance.value = {
      ...attendance.value,
      photo: photoData
    };
    
    stopCamera();
  } catch (err) {
    console.error('Error mengambil foto:', err);
    alert('Gagal mengambil foto. Mohon coba lagi.');
  }
}

const calculatePercentages = () => {
  statistics.value.percentages = {
    present: Math.round((statistics.value.present_days / statistics.value.total_days) * 100),
    late: Math.round((statistics.value.late_days / statistics.value.total_days) * 100),
    leave: Math.round((statistics.value.leave_days / statistics.value.total_days) * 100)
  }
}

const checkIn = async () => {
  if (!attendance.value.location) {
    alert('Mohon izinkan akses lokasi terlebih dahulu');
    return;
  }

  if (!attendance.value.photo) {
    alert('Mohon ambil foto terlebih dahulu');
    return;
  }

  try {
    const response = await axios.post('/pegawai/api/attendance/check-in', {
      latitude: attendance.value.location.latitude,
      longitude: attendance.value.location.longitude,
      photo: attendance.value.photo
    });

    const checkInTime = new Date(response.data.data.check_in);
    const lateTime = new Date();
    lateTime.setHours(7, 0, 0, 0);

    if (checkInTime > lateTime) {
      statistics.value.late_days++;
      attendance.value.status = 'Terlambat';
    } else {
      statistics.value.present_days++;
      attendance.value.status = 'Hadir';
    }

    calculatePercentages();
    attendance.value.check_in = response.data.data.check_in;
    alert('Absensi masuk berhasil');
    await fetchAttendanceData();
  } catch (error) {
    console.error('Error checking in:', error);
    alert(error.response?.data?.message || 'Terjadi kesalahan saat absensi masuk');
  }
}

const checkOut = async () => {
  if (!attendance.value.location) {
    alert('Mohon izinkan akses lokasi terlebih dahulu');
    return;
  }

  if (!attendance.value.photo) {
    alert('Mohon ambil foto terlebih dahulu');
    return;
  }

  try {
    const response = await axios.post('/pegawai/api/attendance/check-out', {
      latitude: attendance.value.location.latitude,
      longitude: attendance.value.location.longitude,
      photo: attendance.value.photo
    });

    attendance.value.check_out = response.data.data.check_out;
    alert('Absensi pulang berhasil');
    
    // Refresh data absensi dan statistik
    await fetchAttendanceData();
    calculatePercentages();
  } catch (error) {
    console.error('Error checking out:', error);
    alert(error.response?.data?.message || 'Terjadi kesalahan saat absensi pulang');
  }
}
</script>

<template>
  <Head title="Absensi Pegawai" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Absensi
      </h2>
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

            <!-- Lokasi -->
            <div class="mb-4">
              <div class="p-3 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-600">
                  <div class="font-semibold mb-1">Lokasi Saat Ini:</div>
                  <div v-if="attendance.location">
                    <div>Latitude: {{ attendance.location.latitude?.toFixed(6) }}</div>
                    <div>Longitude: {{ attendance.location.longitude?.toFixed(6) }}</div>
                    <div class="mt-2 text-xs text-gray-500">
                      Lokasi akan diperbarui secara otomatis
                    </div>
                  </div>
                  <div v-else class="text-yellow-600">
                    Menunggu deteksi lokasi...
                  </div>
                </div>
              </div>
            </div>

            <!-- Kamera -->
            <div class="mb-4">
              <button 
                @click="showCamera ? stopCamera() : startCamera()"
                class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 w-full"
              >
                <i class="fas fa-camera mr-2"></i>
                {{ showCamera ? 'Tutup Kamera' : 'Buka Kamera' }}
              </button>
              
              <div v-if="showCamera" class="mt-4">
                <div class="relative">
                  <video 
                    ref="videoRef" 
                    autoplay 
                    playsinline
                    muted
                    class="w-full rounded-lg border-2 border-gray-300"
                    style="transform: scaleX(-1); width: 100%; height: 240px; object-fit: cover;" 
                  ></video>
                  <button 
                    @click="stopCamera"
                    class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600"
                    title="Tutup Kamera"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="flex space-x-2 mt-2">
                  <button 
                    @click="capturePhoto"
                    class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
                  >
                    <i class="fas fa-camera mr-2"></i>
                    Ambil Foto
                  </button>
                  <button 
                    @click="stopCamera"
                    class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                  >
                    <i class="fas fa-times mr-2"></i>
                    Tutup Kamera
                  </button>
                </div>
              </div>

              <div v-if="attendance.photo" class="mt-4">
                <div class="p-3 bg-gray-50 rounded-lg">
                  <div class="font-semibold mb-2">Foto Absensi:</div>
                  <img 
                    :src="attendance.photo" 
                    class="w-full rounded-lg border-2 border-gray-300"
                    style="width: 100%; height: 240px; object-fit: cover;"
                    alt="Foto Absensi"
                  />
                </div>
              </div>
            </div>

            <div class="flex space-x-4">
              <button 
                @click="checkIn" 
                :disabled="!attendance.location || !attendance.photo || attendance.check_in"
                class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50"
              >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Check In
              </button>
              
              <button 
                @click="checkOut" 
                :disabled="!attendance.location || !attendance.photo || !attendance.check_in || attendance.check_out"
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
                    'bg-red-100 text-red-800': record.status === 'Cuti'
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
  </AuthenticatedLayout>
</template>

<style scoped>
/* Hapus definisi CSS manual dan gunakan kelas Tailwind langsung di template */
</style>
