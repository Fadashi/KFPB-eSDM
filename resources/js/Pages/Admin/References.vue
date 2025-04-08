<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

</script>

<template>
  <Head title="Admin References"/>

  <AuthenticatedLayout>
    <template #header>
      <h1 class="text-xl font-semibold text-gray-900">Referensi</h1>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
      <div class="bg-blue-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-briefcase mr-2 text-blue-600"></i> Data Jabatan
        </h2>
        <p>Kelola jabatan dan tanggung jawab karyawan.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-green-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-sitemap mr-2 text-green-600"></i> Data Bagian
        </h2>
        <p>Informasi tentang struktur organisasi.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-yellow-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-clock mr-2 text-yellow-600"></i> Data Shift
        </h2>
        <p>Detail jam kerja dan pembagian shift.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-purple-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-praying-hands mr-2 text-purple-600"></i> Data Agama
        </h2>
        <p>Data agama karyawan untuk administrasi.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-red-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-users-cog mr-2 text-red-600"></i> Data Struktur
        </h2>
        <p>Hierarki dan hubungan antar jabatan.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-teal-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-user-clock mr-2 text-teal-600"></i> Data Libur
        </h2>
        <p>Hari libur dan cuti bersama.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-orange-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-calendar-alt mr-2 text-orange-600"></i> Data Cuti
        </h2>
        <p>Jenis cuti dan prosedur pengajuan.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-indigo-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-user-tag mr-2 text-indigo-600"></i> Status Pegawai
        </h2>
        <p>Status kepegawaian karyawan.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-pink-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-university mr-2 text-pink-600"></i> Data Bank
        </h2>
        <p>Data bank untuk penggajian.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-gray-100 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-chart-line mr-2 text-gray-600"></i> Data Grading
        </h2>
        <p>Sistem grading untuk penilaian karyawan.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-blue-200 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-user-tie mr-2 text-blue-600"></i> Jabatan Struktural
        </h2>
        <p>Jabatan struktural dan tanggung jawab.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-green-200 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-user-friends mr-2 text-green-600"></i> Jabatan Fungsional
        </h2>
        <p>Peran jabatan fungsional di perusahaan.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-yellow-200 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-file-alt mr-2 text-yellow-600"></i> Data Eselon
        </h2>
        <p>Kriteria jabatan eselon dalam organisasi.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-purple-200 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-file-invoice mr-2 text-purple-600"></i> Data Berkas
        </h2>
        <p>Berkas penting yang harus dikelola.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
      <div class="bg-red-200 p-4 rounded-lg shadow transition-transform transform hover:scale-105 hover:shadow-lg">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-money-check-alt mr-2 text-red-600"></i> Data Payroll
        </h2>
        <p>Informasi penggajian dan tunjangan karyawan.</p>
        <router-link to="#" class="text-blue-500 hover:underline font-bold cursor-pointer">Lihat Detail</router-link>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped></style>
