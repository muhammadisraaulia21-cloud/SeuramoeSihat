<template>
  <div class="p-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Selamat datang, {{ authStore.namaUser }}</p>
    </div>

    <!-- Stats -->
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
      <div v-for="i in 6" :key="i" class="bg-white rounded-2xl p-5 border border-gray-100 animate-pulse h-24" />
    </div>
    <div v-else class="grid grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white rounded-2xl p-5 border border-gray-100"
      >
        <div class="flex items-center justify-between mb-3">
          <span class="text-2xl">{{ stat.icon }}</span>
          <span
            class="text-xs px-2 py-1 rounded-full font-medium"
            :class="stat.badgeClass"
          >{{ stat.badge }}</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ stat.value }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
      </div>
    </div>

    <!-- Shortcut ke antrian hari ini -->
    <div class="bg-emerald-600 rounded-2xl p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-lg font-semibold mb-1">Kelola Antrian Hari Ini</div>
          <div class="text-emerald-100 text-sm">Approve, panggil, atau selesaikan antrian pasien</div>
        </div>
        <RouterLink
          to="/admin/antrian"
          class="bg-white text-emerald-700 font-medium text-sm px-5 py-2.5 rounded-xl hover:bg-emerald-50 transition-colors whitespace-nowrap"
        >
          Buka Antrian →
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../api/axios'

const authStore = useAuthStore()
const loading = ref(true)
const data = ref({})

onMounted(async () => {
  try {
    const res = await api.get('/admin/dashboard')
    data.value = res.data.data
  } catch {
    // silent
  } finally {
    loading.value = false
  }
})

const stats = computed(() => [
  {
    icon: '👥', label: 'Total Pasien',       value: data.value.total_pasien ?? 0,
    badge: 'Terdaftar', badgeClass: 'bg-blue-50 text-blue-600',
  },
  {
    icon: '👨‍⚕️', label: 'Total Dokter',       value: data.value.total_dokter ?? 0,
    badge: 'Aktif', badgeClass: 'bg-emerald-50 text-emerald-600',
  },
  {
    icon: '📋', label: 'Antrian Hari Ini',   value: data.value.antrian_hari_ini ?? 0,
    badge: 'Hari ini', badgeClass: 'bg-purple-50 text-purple-600',
  },
  {
    icon: '⏳', label: 'Menunggu',           value: data.value.antrian_menunggu ?? 0,
    badge: 'Pending', badgeClass: 'bg-yellow-50 text-yellow-600',
  },
  {
    icon: '✅', label: 'Selesai',            value: data.value.antrian_selesai ?? 0,
    badge: 'Done', badgeClass: 'bg-emerald-50 text-emerald-600',
  },
  {
    icon: '❌', label: 'Dibatalkan',         value: data.value.antrian_dibatalkan ?? 0,
    badge: 'Batal', badgeClass: 'bg-red-50 text-red-600',
  },
])
</script>
