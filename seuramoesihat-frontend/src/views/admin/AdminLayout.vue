<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside class="w-60 bg-white border-r border-gray-100 flex flex-col fixed h-full z-40">
      <!-- Logo -->
      <div class="px-5 py-5 border-b border-gray-100">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center">
            <img src="/logo.png" alt="SeuramoeSihat" class="w-full h-full object-contain" />
          </div>
          <div>
            <div class="text-sm font-semibold text-gray-800">SeuramoeSihat</div>
            <div class="text-xs text-emerald-600 font-medium">Admin Panel</div>
          </div>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-4 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200"
          :class="
            $route.path === item.to
              ? 'bg-emerald-50 text-emerald-700 font-medium'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800'
          "
        >
          <span class="text-base">{{ item.icon }}</span>
          {{ item.label }}
        </RouterLink>
      </nav>

      <!-- User info + logout -->
      <div class="px-4 py-4 border-t border-gray-100">
        <div class="text-xs text-gray-500 mb-1">Login sebagai</div>
        <div class="text-sm font-medium text-gray-800 truncate mb-3">{{ authStore.namaUser }}</div>
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-red-600 hover:bg-red-50 transition-colors"
        >
          <span>🚪</span> Keluar
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 ml-60 min-h-screen">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const navItems = [
  { to: '/admin',          icon: '📊', label: 'Dashboard'   },
  { to: '/admin/antrian',  icon: '📋', label: 'Antrian'     },
  { to: '/admin/dokter',   icon: '👨‍⚕️', label: 'Dokter'      },
  { to: '/admin/pasien',   icon: '👥', label: 'Pasien'      },
]

async function handleLogout() {
  try {
    await authStore.logout()
  } catch {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }
  router.push('/login')
}
</script>
