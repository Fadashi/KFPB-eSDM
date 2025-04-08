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

  </AuthenticatedLayout>
</template>
