<script setup>
import { ref, watch } from 'vue';
import Topbar from '@/Components/Topbar.vue';
import Sidebar from '@/Components/Sidebar.vue';

const showingNavigationDropdown = ref(false);
const sidebarWidth = ref('250px');

// Tambahkan method untuk mengubah lebar konten saat sidebar collapse
const handleSidebarCollapse = (isCollapsed) => {
  sidebarWidth.value = isCollapsed ? '70px' : '250px';
};
</script>

<template>
    <div class="flex">
        <!-- Sidebar Component -->
        <Sidebar @sidebar-toggle="handleSidebarCollapse" />

        <div class="flex-1" :style="{ marginLeft: sidebarWidth }">
            <!-- Topbar Component -->
            <Topbar />

            <!-- Page Content -->
            <div class="min-h-screen bg-gray-100">
                <!-- Page Heading -->
                <header
                    class="bg-white shadow"
                    v-if="$slots.header"
                >
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Main Content -->
                <main class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
.flex-1 {
  transition: margin-left 0.3s ease;
}
</style>
