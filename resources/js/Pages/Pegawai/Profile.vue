<script setup>
import { ref } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue'

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

const user = usePage().props.auth.user
const isEdit = ref(false)
const notif = ref('')
const form = ref({
  name: user.name,
  email: user.email,
  password: '',
  password_confirmation: ''
})
const errors = ref({})

function openEdit() {
  isEdit.value = true
  notif.value = ''
  errors.value = {}
  form.value.name = user.name
  form.value.email = user.email
  form.value.password = ''
  form.value.password_confirmation = ''
}

async function submitEdit() {
  notif.value = ''
  errors.value = {}
  try {
    await router.patch('/profile', form.value, {
      onSuccess: () => {
        notif.value = 'Profil berhasil diperbarui!'
        isEdit.value = false
      },
      onError: (err) => {
        errors.value = err
      }
    })
  } catch (e) {
    notif.value = 'Gagal memperbarui profil.'
  }
}
</script>

<template>
  <Head title="Profil Pegawai" />
  <AuthenticatedLayout>
    <template #header>
      <Breadcrumb :items="[
        { label: 'Dashboard', href: '/pegawai/dashboard', icon: 'fas fa-home' },
        { label: 'Profil' }
      ]" />
      <h1 class="text-xl font-semibold text-gray-900">Profil Pegawai</h1>
    </template>
    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex flex-col items-center">
            <img src="/images/user.png" alt="User" class="w-24 h-24 rounded-full mb-4 border-2 border-blue-200">
            <h2 class="text-2xl font-bold mb-2">{{ user.name }}</h2>
            <p class="text-gray-600 mb-1">NIP: {{ user.nip }}</p>
            <p class="text-gray-600 mb-1">Email: {{ user.email }}</p>
            <p class="text-gray-600 mb-1">Role: {{ user.role }}</p>
            <button @click="openEdit" class="mt-4 px-5 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">Edit Profil</button>
          </div>
          <div v-if="notif" class="mt-4 p-2 rounded bg-green-100 text-green-700 text-center font-bold">{{ notif }}</div>
          <div v-if="isEdit" class="mt-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">Edit Profil</h3>
            <form @submit.prevent="submitEdit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input v-model="form.name" type="text" class="input w-full" />
                <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="form.email" type="email" class="input w-full" />
                <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (opsional)</label>
                <input v-model="form.password" type="password" class="input w-full" />
                <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input v-model="form.password_confirmation" type="password" class="input w-full" />
                <p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ errors.password_confirmation }}</p>
              </div>
              <div class="flex gap-2 justify-end mt-4">
                <button type="button" @click="isEdit=false" class="px-5 py-2 rounded bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded bg-blue-600 text-white font-bold hover:bg-blue-700 transition">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.input {
  @apply block w-full rounded-md border-gray-300 shadow-sm py-3 px-4;
}
</style>
