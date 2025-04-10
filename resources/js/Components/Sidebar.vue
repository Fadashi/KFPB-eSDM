<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const user = usePage().props.auth.user

const menuItems = computed(() => {
  switch (user.role) {
    case 'admin':
      return [
        { icon: 'fas fa-tachometer-alt', text: 'Dashboard', route: 'dashboard' },
        { icon: 'fas fa-database', text: 'Referensi', route: 'admin.references' },
        { icon: 'fas fa-users', text: 'Karyawan', route: 'admin.employees' },
        { icon: 'fas fa-clock', text: 'Absensi', route: 'admin.attendance' },
        { icon: 'fas fa-file', text: 'Laporan', route: 'admin.report' },
        { icon: 'fas fa-book', text: 'Audit Trail', route: 'admin.audit-trail' },
        { icon: 'fas fa-cog', text: 'Pengaturan', route: 'admin.settings' },
      ]
    case 'atasan':
      return [
        { icon: 'fas fa-tachometer-alt', text: 'Dashboard', route: 'dashboard' },
        { icon: 'fas fa-users', text: 'Tim Saya', route: '#' },
        { icon: 'fas fa-check-circle', text: 'Persetujuan', route: '#' },
        { icon: 'fas fa-file-alt', text: 'Laporan', route: '#' },
      ]
    case 'pegawai':
      return [
        { icon: 'fas fa-tachometer-alt', text: 'Dashboard', route: 'dashboard' },
        { icon: 'fas fa-user-clock', text: 'Absensi', route: '#' },
        { icon: 'fas fa-calendar-alt', text: 'Pengajuan Cuti', route: '#' },
        { icon: 'fas fa-history', text: 'Riwayat', route: '#' },
      ]
    default:
      return []
  }
})

const isCollapsed = ref(false)

const emit = defineEmits(['sidebar-toggle'])

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
  emit('sidebar-toggle', isCollapsed.value)
}

const isActiveRoute = (routeName) => {
  return routeName !== '#' && route().current(routeName)
}
</script>

<template>
  <div class="sidebar" :class="{ collapsed: isCollapsed }">
    <div class="logo-container">
      <Link :href="route('dashboard')" class="logo">Logo</Link>
      <h2 v-if="!isCollapsed">Admin Panel</h2>
    </div>

    <button class="toggle-btn" @click="toggleSidebar">
      <i v-if="!isCollapsed" class="fas fa-angle-left"></i>
      <i v-else class="fas fa-angle-right"></i>
    </button>

    <nav class="menu">
      <Link
        v-for="item in menuItems"
        :key="item.route"
        :href="item.route !== '#' ? route(item.route) : '#'"
        class="menu-item"
        :class="{ active: isActiveRoute(item.route) }"
      >
        <i :class="item.icon"></i>
        <span v-if="!isCollapsed">{{ item.text }}</span>
      </Link>
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
  width: 30px;
  height: 30px;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-btn i {
  font-size: 16px;
  color: #1a237e;
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
