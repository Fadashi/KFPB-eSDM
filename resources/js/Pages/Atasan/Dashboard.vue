<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue'

const teamStats = ref({
  totalMembers: 15,
  presentToday: 13,
  onLeave: 1,
  late: 1,
})

const pendingApprovals = ref([
  { id: 1, name: 'Budi Santoso', type: 'Cuti Tahunan', duration: '3 hari', status: 'pending' },
  { id: 2, name: 'Ani Wijaya', type: 'Izin Sakit', duration: '1 hari', status: 'pending' },
])

const teamAttendance = ref([
  { id: 1, name: 'Budi Santoso', position: 'Frontend Developer', status: 'Hadir', time: '08:00' },
  { id: 2, name: 'Ani Wijaya', position: 'UI/UX Designer', status: 'Terlambat', time: '08:45' },
  { id: 3, name: 'Dedi Kurniawan', position: 'Backend Developer', status: 'Hadir', time: '07:55' },
])
</script>

<template>
  <Head title="Atasan Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Dashboard Tim
      </h2>
    </template>

    <div class="p-6 space-y-6">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="fas fa-users"></i>
          <div class="stat-info">
            <h3>Total Anggota Tim</h3>
            <p>{{ teamStats.totalMembers }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="fas fa-check-circle"></i>
          <div class="stat-info">
            <h3>Hadir Hari Ini</h3>
            <p>{{ teamStats.presentToday }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="fas fa-clock"></i>
          <div class="stat-info">
            <h3>Terlambat</h3>
            <p>{{ teamStats.late }}</p>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow flex items-center gap-4">
          <i class="fas fa-calendar-minus"></i>
          <div class="stat-info">
            <h3>Izin/Cuti</h3>
            <p>{{ teamStats.onLeave }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Persetujuan yang Pending -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-semibold mb-4">Persetujuan Pending</h2>
          <div class="space-y-4">
            <div v-for="approval in pendingApprovals" :key="approval.id" 
                 class="flex items-center justify-between p-4 border rounded-lg">
              <div>
                <h3 class="font-medium">{{ approval.name }}</h3>
                <p class="text-sm text-gray-600">{{ approval.type }} - {{ approval.duration }}</p>
              </div>
              <div class="flex gap-2">
                <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                  <i class="fas fa-check"></i>
                </button>
                <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Kehadiran Tim Hari Ini -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-semibold mb-4">Kehadiran Tim Hari Ini</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="border-b">
                  <th class="text-left py-2">Nama</th>
                  <th class="text-left py-2">Posisi</th>
                  <th class="text-left py-2">Status</th>
                  <th class="text-left py-2">Waktu</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="member in teamAttendance" :key="member.id" class="border-b">
                  <td class="py-2">{{ member.name }}</td>
                  <td class="py-2">{{ member.position }}</td>
                  <td class="py-2">
                    <span class="px-2 py-1 rounded text-sm" :class="{
                      'bg-green-100 text-green-800': member.status === 'Hadir',
                      'bg-yellow-100 text-yellow-800': member.status === 'Terlambat',
                      'bg-red-100 text-red-800': member.status === 'Tidak Hadir'
                    }">
                      {{ member.status }}
                    </span>
                  </td>
                  <td class="py-2">{{ member.time }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Hapus definisi CSS manual dan gunakan kelas Tailwind langsung di template */
</style>
