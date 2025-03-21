<script setup>
import { ref } from 'vue'

const currentUser = ref({
  name: 'Admin User',
  avatar: 'https://via.placeholder.com/40',
})

const notifications = ref(3)
</script>

<template>
  <div class="topbar">
    <div class="search-bar">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Cari..." />
    </div>

    <div class="right-items">
      <div class="notification-icon">
        <i class="fas fa-bell"></i>
        <span class="badge" v-if="notifications">{{ notifications }}</span>
      </div>

      <div class="user-profile">
        <img :src="currentUser.avatar" :alt="currentUser.name" />
        <span>{{ currentUser.name }}</span>
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
}

.badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff4081;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 12px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.user-profile img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}
</style>
