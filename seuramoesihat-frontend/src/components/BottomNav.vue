<template>
  <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 flex md:hidden z-50">
    <RouterLink
      v-for="item in items"
      :key="item.to"
      :to="item.to"
      class="flex-1 flex flex-col items-center gap-1 py-3 text-xs transition-colors relative"
      :class="active === item.key ? 'text-emerald-600' : 'text-gray-400 hover:text-gray-600'"
    >
      <span class="text-xl relative">
        {{ item.icon }}
        <!-- Badge notifikasi belum dibaca -->
        <span
          v-if="item.key === 'notifikasi' && notifStore.totalUnread > 0"
          class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 leading-none"
        >{{ notifStore.totalUnread > 99 ? '99+' : notifStore.totalUnread }}</span>
      </span>
      {{ item.label }}
    </RouterLink>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useNotifikasiStore } from '../stores/notifikasi'

defineProps({ active: String })

const notifStore = useNotifikasiStore()

// Fetch jumlah unread setiap kali BottomNav dimount
onMounted(() => {
  notifStore.fetch()
})

const items = [
  { key: 'home',        to: '/',            icon: '🏠', label: 'Beranda'   },
  { key: 'cari',        to: '/cari-dokter', icon: '🔍', label: 'Cari'      },
  { key: 'antrian',     to: '/antrian',     icon: '📋', label: 'Antrian'   },
  { key: 'notifikasi',  to: '/notifikasi',  icon: '🔔', label: 'Notifikasi'},
  { key: 'profil',      to: '/profil',      icon: '👤', label: 'Profil'    },
]
</script>
