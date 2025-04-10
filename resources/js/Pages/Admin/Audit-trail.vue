<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
                        Absensi
                    </li>
                </ol>
            </nav>

            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Audit Trail</h1>
            </div>
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
