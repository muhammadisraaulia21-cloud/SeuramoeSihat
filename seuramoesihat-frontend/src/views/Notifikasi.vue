<template>
  <div class="min-h-screen bg-gray-50 pb-24">
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button @click="$router.back()"
            class="w-9 h-9 border border-gray-200 rounded-xl flex items-center justify-center hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="text-sm font-semibold text-gray-800">Notifikasi</span>
        </div>
        <button @click="store.bacaSemua()" class="text-xs text-emerald-600 font-medium hover:text-emerald-700 transition-colors">
          Tandai semua dibaca
        </button>
      </div>
    </nav>

    <div class="max-w-3xl mx-auto px-6 py-6">
      <!-- Filter -->
      <div class="flex gap-2 mb-6 overflow-x-auto pb-1 scrollbar-hide">
        <button v-for="f in filters" :key="f.key" @click="activeFilter = f.key; store.fetch(f.key === 'semua' ? '' : f.key)"
          class="flex-shrink-0 text-xs px-4 py-2 rounded-full border transition-all duration-200"
          :class="activeFilter === f.key ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-300'">
          {{ f.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="space-y-3">
        <div v-for="i in 4" :key="i" class="bg-white rounded-2xl p-4 border border-gray-100 animate-pulse flex gap-4">
          <div class="w-11 h-11 bg-gray-200 rounded-2xl flex-shrink-0"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 rounded w-full"></div>
          </div>
        </div>
      </div>

      <!-- Notif list -->
      <div v-else-if="store.grouped.length > 0" class="space-y-2">
        <div v-for="group in store.grouped" :key="group.tanggal">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3 mt-5 first:mt-0">
            {{ group.tanggal }}
          </p>
          <div class="space-y-2">
            <div v-for="notif in group.items" :key="notif.id" @click="klikNotif(notif)"
              class="bg-white rounded-2xl p-4 border transition-all duration-200 cursor-pointer flex gap-4"
              :class="!notif.dibaca ? 'border-emerald-100 shadow-sm' : 'border-gray-100 hover:border-gray-200'">
              <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0"
                :class="notif.bg_class">
                {{ notif.icon }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <p class="text-sm font-semibold text-gray-800">{{ notif.judul }}</p>
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-xs text-gray-400">{{ notif.waktu }}</span>
                    <span v-if="!notif.dibaca" class="w-2 h-2 bg-emerald-500 rounded-full flex-shrink-0"></span>
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ notif.pesan }}</p>
                <div class="flex gap-2 mt-2" v-if="notif.aksi">
                  <RouterLink v-if="notif.aksi_url" :to="notif.aksi_url"
                    class="text-xs bg-emerald-600 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-700 transition-colors">
                    {{ notif.aksi }}
                  </RouterLink>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="text-center py-20">
        <div class="text-5xl mb-4">🔔</div>
        <p class="text-gray-500 text-sm font-medium">Tidak ada notifikasi</p>
        <p class="text-gray-400 text-xs mt-1">Notifikasi baru akan muncul di sini</p>
      </div>
    </div>

    <BottomNav active="notifikasi" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifikasiStore } from '../stores/notifikasi'
import BottomNav from '../components/BottomNav.vue'

const router = useRouter()
const store = useNotifikasiStore()
const activeFilter = ref('semua')

const filters = [
  { key: 'semua',     label: 'Semua' },
  { key: 'antrian',   label: 'Antrian' },
  { key: 'chat',      label: 'Chat' },
  { key: 'kesehatan', label: 'Kesehatan' },
  { key: 'sistem',    label: 'Sistem' },
]

function klikNotif(notif) {
  store.baca(notif.id)
  if (notif.aksi_url) router.push(notif.aksi_url)
}

onMounted(() => store.fetch())
</script>
