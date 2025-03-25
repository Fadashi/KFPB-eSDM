<script setup>
import { ref } from 'vue';
import Topbar from '@/Components/Topbar.vue';
import Sidebar from '@/Components/Sidebar.vue';

const sidebarCollapsed = ref(false);

// Handle sidebar toggle event
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};
</script>

<template>
  <div class="flex">
    <!-- Sidebar -->
    <Sidebar @sidebar-toggle="handleSidebarCollapse" />

    <div class="flex-1 transition-all duration-300" :class="sidebarCollapsed ? 'ml-[70px]' : 'ml-[250px]'">
      <!-- Topbar -->
      <Topbar />

      <!-- Page Content -->
      <div class="min-h-screen bg-gray-100">
        <header v-if="$slots.header" class="bg-white shadow">
          <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <slot name="header" />
          </div>
        </header>

        <main class="py-6">
          <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <slot />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>
