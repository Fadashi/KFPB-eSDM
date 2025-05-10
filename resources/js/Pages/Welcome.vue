<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Landing Page ESDM KFPB" />
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
        <!-- Navigation -->
        <nav class="bg-white/90 shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <img src="/images/KF_Logo.png" alt="Kimia Farma Logo" class="h-8 w-auto" />
                        <span class="ml-2 text-xl font-bold text-gray-800">ESDM Kimia Farma</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative overflow-hidden min-h-screen">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img src="/images/BG_KF1.jpg" alt="Background" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 to-blue-800/80"></div>
            </div>
            
            <div class="max-w-7xl mx-auto relative">
                <!-- Hero Content -->
                <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                    <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8">
                        <div class="sm:text-left">
                            <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                                <span class="block xl:inline">Sistem Manajemen</span> <br>
                                <span class="block text-blue-200 xl:inline">SDM Kimia Farma Plant Banjaran</span>
                            </h1>
                            <p class="mt-3 text-base text-gray-100 sm:mt-5 sm:text-lg sm:max-w-xl md:mt-5 md:text-xl">
                                Platform terpadu untuk pengelolaan Sumber Daya Manusia di Kimia Farma. Tingkatkan efisiensi dan produktivitas dengan sistem manajemen SDM yang terintegrasi.
                            </p>
                            <div class="mt-5 sm:mt-8 sm:flex sm:justify-start">
                                <div class="rounded-md shadow">
                                    <Link
                                        v-if="canLogin"
                                        :href="route('login')"
                                        class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10"
                                    >
                                        Mulai Sekarang
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>

                <!-- Feature Section -->
                <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <!-- Feature 1 -->
                        <div class="p-6 bg-white/10 backdrop-blur-md rounded-lg border border-white/20 hover:bg-white/20 transition duration-300">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-white text-center">Manajemen Karyawan</h3>
                            <p class="mt-2 text-base text-gray-200 text-center">Kelola data karyawan dengan mudah dan efisien dalam satu platform terpadu.</p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="p-6 bg-white/10 backdrop-blur-md rounded-lg border border-white/20 hover:bg-white/20 transition duration-300">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-white text-center">Evaluasi Kinerja</h3>
                            <p class="mt-2 text-base text-gray-200 text-center">Monitor dan evaluasi performa karyawan secara sistematis dan terukur.</p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="p-6 bg-white/10 backdrop-blur-md rounded-lg border border-white/20 hover:bg-white/20 transition duration-300">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-white text-center">Pengembangan SDM</h3>
                            <p class="mt-2 text-base text-gray-200 text-center">Dukung pertumbuhan karyawan melalui program pengembangan yang terstruktur.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <Footer />
    </div>
</template>
