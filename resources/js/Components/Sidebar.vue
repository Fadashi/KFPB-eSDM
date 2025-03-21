<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const menuItems = ref([
  { icon: 'fas fa-tachometer-alt', text: 'Dashboard', route: '/dashboard' },
  { icon: 'fas fa-user-clock', text: 'Absensi', route: '/attendance' },
  { icon: 'fas fa-users', text: 'Karyawan', route: '/employees' },
  { icon: 'fas fa-calendar-alt', text: 'Jadwal', route: '/schedule' },
  { icon: 'fas fa-file-alt', text: 'Laporan', route: '/reports' },
  { icon: 'fas fa-cog', text: 'Pengaturan', route: '/settings' },
])

const isCollapsed = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

const isActiveRoute = (route) => {
  return useRoute().path === route
}
</script>

<template>
  <div class="sidebar" :class="{ collapsed: isCollapsed }">
    <div class="logo-container">
      <a href="#" class="logo">Logo</a>
      <h2 v-if="!isCollapsed">Admin Panel</h2>
    </div>

    <button class="toggle-btn" @click="toggleSidebar">
      <i :class="isCollapsed ? 'fas fa-angle-right' : 'fas fa-angle-left'"></i>
    </button>

    <nav class="menu">
      <router-link
        v-for="item in menuItems"
        :key="item.route"
        :to="item.route"
        class="menu-item"
        :class="{ active: isActiveRoute(item.route) }"
      >
        <i :class="item.icon"></i>
        <span v-if="!isCollapsed">{{ item.text }}</span>
      </router-link>
    </nav>
  </div>
</template>

<style scoped>
.sidebar {
  width: 250px;
  height: 100vh;
  background: #1a237e;
  color: white;
  transition: all 0.3s ease;
  position: fixed;
  left: 0;
  top: 0;
}

.collapsed {
  width: 70px;
}

.logo-container {
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  width: 40px;
  height: 40px;
  color: white;
  text-decoration: none;
}

.toggle-btn {
  position: absolute;
  right: -12px;
  top: 70px;
  background: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.menu {
  margin-top: 20px;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  text-decoration: none;
  color: rgba(255, 255, 255, 0.7);
  gap: 15px;
  transition: all 0.3s ease;
  position: relative;
}

.menu-item:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.menu-item.active {
  color: white;
  background: rgba(255, 255, 255, 0.2);
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 4px;
  background: #ff4081;
}

.menu-item i {
  font-size: 20px;
  width: 30px;
  text-align: center;
}

.collapsed .menu-item.active::before {
  width: 100%;
  height: 4px;
  top: auto;
  bottom: 0;
}
</style>
