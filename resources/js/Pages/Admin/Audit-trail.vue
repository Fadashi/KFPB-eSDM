<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

</script>

<template>
  <Head title="Admin Audit Trail" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Audit Trail</h2>
    </template>

    <div class="bg-white p-6 rounded-lg shadow mt-6">
      <h2 class="text-lg font-semibold mb-4">Riwayat Audit</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-left py-2 px-4 text-gray-600">Waktu</th>
              <th class="text-left py-2 px-4 text-gray-600">Aksi</th>
              <th class="text-left py-2 px-4 text-gray-600">Data</th>
              <th class="text-left py-2 px-4 text-gray-600">Dilakukan Oleh</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Contoh data, ganti dengan data dinamis -->
            <tr class="hover:bg-gray-100">
              <td class="py-2 px-4">2023-10-01 10:00</td>
              <td class="py-2 px-4">Tambah Karyawan</td>
              <td class="py-2 px-4">John Doe</td>
              <td class="py-2 px-4">Admin</td>
            </tr>
            <tr class="hover:bg-gray-100">
              <td class="py-2 px-4">2023-10-02 11:30</td>
              <td class="py-2 px-4">Update Referensi</td>
              <td class="py-2 px-4">Data Jabatan</td>
              <td class="py-2 px-4">Admin</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
