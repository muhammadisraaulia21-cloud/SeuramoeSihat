<template>
  <div class="min-h-screen bg-gray-50">
    <!-- NAVBAR -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <RouterLink to="/" class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center">
            <img src="/logo.png" alt="SeuramoeSihat" class="w-full h-full object-contain" />
          </div>
          <span class="text-lg font-semibold text-gray-800">SeuramoeSihat</span>
        </RouterLink>
        <div class="flex items-center gap-3">
          <template v-if="authStore.isLoggedIn">
            <span class="text-sm text-gray-600 font-medium hidden md:block">Halo, {{ authStore.namaUser }}</span>
            <button
              @click="handleLogout"
              class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors"
            >Keluar</button>
          </template>
          <template v-else>
            <RouterLink to="/login" class="text-sm text-gray-600 hover:text-emerald-600 font-medium">Masuk</RouterLink>
            <RouterLink to="/register" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">Daftar</RouterLink>
          </template>
        </div>
      </div>
    </nav>

    <!-- HEADER -->
    <div class="bg-white border-b border-gray-100 px-6 py-8">
      <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Cari Dokter</h1>
        <div class="flex gap-3 flex-col md:flex-row">
          <div class="flex-1 flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input v-model="search" @input="onSearch" type="text" placeholder="Nama dokter, spesialis, atau faskes..."
              class="flex-1 text-sm text-gray-700 placeholder:text-gray-400 outline-none bg-transparent" />
          </div>
          <select v-model="selectedWilayah" @change="loadDokter"
            class="border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-600 outline-none bg-white">
            <option value="">Semua Wilayah</option>
            <option>Sigli</option>
            <option>Mila</option>
            <option>Grong-Grong</option>
            <option>Kembang Tanjong</option>
          </select>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">
      <!-- Filter Chips -->
      <div class="flex gap-2 flex-wrap mb-6">
        <button v-for="f in filters" :key="f" @click="activeFilter = f; loadDokter()"
          class="text-xs px-4 py-2 rounded-full border transition-all duration-200"
          :class="activeFilter === f ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-300'">
          {{ f }}
        </button>
      </div>

      <!-- Result count & sort -->
      <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500">
          Menampilkan <span class="font-medium text-gray-800">{{ store.list.length }}</span> dokter
        </p>
        <select v-model="sortBy" @change="loadDokter"
          class="text-xs border border-gray-200 rounded-lg px-3 py-2 outline-none bg-white text-gray-600">
          <option value="rating">Rating tertinggi</option>
          <option value="kuota">Kuota tersedia</option>
          <option value="nama">Nama A-Z</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="i in 6" :key="i" class="bg-white rounded-2xl p-5 border border-gray-100 animate-pulse">
          <div class="flex gap-4 mb-4">
            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
          <div class="h-8 bg-gray-200 rounded-xl"></div>
        </div>
      </div>

      <!-- Cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="dokter in store.list" :key="dokter.id"
          class="bg-white rounded-2xl p-5 border border-gray-100 hover:border-emerald-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
          <div class="flex items-start gap-4 mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0"
              :style="{ background: dokter.avatar_bg, color: dokter.avatar_color }">
              {{ dokter.inisial }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold text-gray-800">{{ dokter.nama }}</div>
              <div class="text-xs text-gray-500 mt-0.5">{{ dokter.spesialis }}</div>
              <div class="text-xs text-gray-400 mt-0.5">{{ dokter.faskes }}</div>
            </div>
            <Badge :variant="dokter.tersedia ? 'success' : 'destructive'" appearance="light" class="flex-shrink-0">
              {{ dokter.tersedia ? 'Tersedia' : 'Penuh' }}
            </Badge>
          </div>
          <div class="flex items-center gap-2 mb-4 flex-wrap">
            <Badge variant="warning" appearance="light" size="sm">⭐ {{ dokter.rating }}</Badge>
            <Badge variant="secondary" appearance="light" size="sm">{{ dokter.jadwal }}</Badge>
            <Badge variant="info" appearance="outline" size="sm">Sisa {{ dokter.kuota }} kuota</Badge>
          </div>
          <div class="flex gap-2">
            <RouterLink :to="'/dokter/' + dokter.id"
              class="flex-1 py-2.5 rounded-xl text-xs font-medium border border-emerald-200 text-emerald-600 hover:bg-emerald-50 transition-colors text-center">
              Lihat Profil
            </RouterLink>
            <button :disabled="!dokter.tersedia" @click="bookingDokter(dokter)"
              class="flex-2 px-4 py-2.5 rounded-xl text-xs font-medium transition-all duration-200"
              :class="dokter.tersedia ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
              {{ dokter.tersedia ? 'Booking' : 'Penuh' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-if="store.error" class="text-center py-10">
        <p class="text-red-500 text-sm">{{ store.error }}</p>
        <button @click="loadDokter" class="mt-3 text-xs text-emerald-600 underline">Coba lagi</button>
      </div>

      <div v-if="!store.loading && !store.error && store.list.length === 0" class="text-center py-20">
        <div class="text-4xl mb-4">🔍</div>
        <p class="text-gray-500 text-sm">Dokter tidak ditemukan</p>
        <p class="text-gray-400 text-xs mt-1">Coba ubah kata kunci atau filter</p>
      </div>
    </div>

    <BottomNav active="cari" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDokterStore } from '../stores/dokter'
import { useAuthStore } from '../stores/auth'
import BottomNav from '../components/BottomNav.vue'
import Badge from '../components/ui/Badge.vue'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  try {
    await authStore.logout()
  } catch {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }
  router.push('/login')
}
const store = useDokterStore()

const search = ref('')
const selectedWilayah = ref('')
const activeFilter = ref('Semua')
const sortBy = ref('rating')

const filters = ['Semua', 'Dokter Umum', 'Spesialis Anak', 'Gigi', 'Kandungan', 'Penyakit Dalam']

let searchTimeout = null

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(loadDokter, 400)
}

function loadDokter() {
  const params = { sort: sortBy.value }
  if (search.value) params.search = search.value
  if (activeFilter.value !== 'Semua') params.kategori = activeFilter.value
  if (selectedWilayah.value) params.wilayah = selectedWilayah.value
  store.fetchList(params)
}

function bookingDokter(dokter) {
  router.push('/booking/' + dokter.id)
}

onMounted(loadDokter)
</script>
