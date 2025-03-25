<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

const user = usePage().props.auth.user
const notifications = ref(3)
const messages = ref(5)
const showNotifications = ref(false)

// Contoh daftar notifikasi
const notificationsList = ref([
  { id: 1, text: 'Pesanan Anda telah dikonfirmasi', link: '#' },
  { id: 2, text: 'Promo spesial hari ini! Cek sekarang.', link: '#' },
  { id: 3, text: 'Peringatan: Saldo dompet digital Anda menipis.', link: '#' },
])
</script>

<template>
  <div class="topbar">
    <div class="search-bar">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Cari..." />
    </div>

    <div class="right-items">
      <!-- Notifikasi Pesan -->
      <div class="relative">
        <div class="notification-icon">
          <i class="fas fa-envelope"></i>
          <span class="badge" v-if="messages">{{ messages }}</span>
        </div>
      </div>

      <!-- Notifikasi dengan Dropdown -->
      <div class="relative">
        <div class="notification-icon" @click="showNotifications = !showNotifications">
          <i class="fas fa-bell"></i>
          <span class="badge" v-if="notifications">{{ notifications }}</span>
        </div>

        <!-- Dropdown Notifikasi -->
        <div v-if="showNotifications" class="dropdown-menu">
          <div v-if="notificationsList.length">
            <DropdownLink
              v-for="notif in notificationsList"
              :key="notif.id"
              :href="notif.link">
              {{ notif.text }}
            </DropdownLink>
          </div>
          <div v-else class="p-4 text-gray-500 text-sm">
            Tidak ada notifikasi baru.
          </div>
        </div>
      </div>

      <!-- User Profile dengan Dropdown -->
      <div class="relative">
        <Dropdown align="right" width="48">
          <template #trigger>
            <div class="user-profile">
              <img
                :src="user.profile_photo_url || 'https://ui-avatars.com/api/?name=' + user.name"
                :alt="user.name"
              />
              <span>{{ user.name }}</span>
              <i class="fas fa-chevron-down ml-2 text-sm"></i>
            </div>
          </template>

          <template #content>
            <div class="px-4 py-2 text-xs text-gray-400">
              {{ user.email }}
            </div>

            <DropdownLink :href="route('profile.edit')">
              <i class="fas fa-user mr-2"></i>
              Profile
            </DropdownLink>

            <div class="border-t border-gray-200"></div>

            <DropdownLink
              :href="route('logout')"
              method="post"
              as="button"
              class="w-full text-left"
            >
              <i class="fas fa-sign-out-alt mr-2"></i>
              Log Out
            </DropdownLink>
          </template>
        </Dropdown>
      </div>
    </div>
  </div>
</template>

<style scoped>
.topbar {
  height: 64px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-bar {
  display: flex;
  align-items: center;
  background: #f5f5f5;
  padding: 8px 15px;
  border-radius: 20px;
  width: 300px;
}

.search-bar input {
  border: none;
  background: none;
  margin-left: 10px;
  outline: none;
  width: 100%;
}

.right-items {
  display: flex;
  align-items: center;
  gap: 20px;
}

.notification-icon {
  position: relative;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.notification-icon:hover {
  background-color: #f5f5f5;
}

.notification-icon i {
  font-size: 1.5rem;
  color: #666;
}

.badge {
  position: absolute;
  top: 4px; /* Atur posisi vertikal */
  right: 4px; /* Atur posisi horizontal */
  background: #ff4081;
  color: white;
  border-radius: 50%;
  padding: 4px 6px;
  font-size: 12px;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  transform: translate(50%, -50%);
}

/* Dropdown Notifikasi */
.dropdown-menu {
  position: absolute;
  top: 50px;
  right: 0;
  width: 250px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  z-index: 50;
}

.dropdown-menu a {
  display: block;
  padding: 10px 15px;
  color: #333;
  text-decoration: none;
  font-size: 14px;
}

.dropdown-menu a:hover {
  background: #f5f5f5;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.user-profile:hover {
  background-color: #f5f5f5;
}

.user-profile img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}
</style>
