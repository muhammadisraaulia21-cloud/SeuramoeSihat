<template>
  <div class="min-h-screen bg-white font-sans">
    <!-- NAVBAR -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center">
            <img src="/logo.png" alt="SeuramoeSihat" class="w-full h-full object-contain" />
          </div>
          <span class="text-lg font-semibold text-gray-800">SeuramoeSihat</span>
        </div>
        <div class="hidden md:flex items-center gap-8">
          <a href="#layanan" class="text-sm text-gray-500 hover:text-emerald-600 transition-colors"
            >Layanan</a
          >
          <a href="#dokter" class="text-sm text-gray-500 hover:text-emerald-600 transition-colors"
            >Dokter</a
          >
          <a
            href="#cara-kerja"
            class="text-sm text-gray-500 hover:text-emerald-600 transition-colors"
            >Cara Kerja</a
          >
        </div>
        <div class="flex items-center gap-3">
          <template v-if="authStore.isLoggedIn">
            <span class="text-sm text-gray-600 font-medium hidden md:block">Halo, {{ authStore.namaUser }}</span>
            <button
              @click="handleLogout"
              class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors"
            >Keluar</button>
          </template>
          <template v-else>
            <RouterLink
              to="/login"
              class="text-sm text-gray-600 hover:text-emerald-600 transition-colors font-medium"
              >Masuk</RouterLink
            >
            <RouterLink
              to="/register"
              class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors"
              >Daftar</RouterLink
            >
          </template>
        </div>
      </div>
    </nav>

    <!-- HERO -->
    <section class="relative bg-white pt-24 pb-28 px-6 overflow-hidden">
      <!-- Background blobs -->
      <div
        class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-50 rounded-full -translate-y-1/2 translate-x-1/3 opacity-70 pointer-events-none"
      />
      <div
        class="absolute bottom-0 left-0 w-80 h-80 bg-emerald-50 rounded-full translate-y-1/2 -translate-x-1/3 opacity-70 pointer-events-none"
      />

      <div class="max-w-4xl mx-auto text-center relative">
        <!-- Badge -->
        <div
          class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-medium px-4 py-2 rounded-full mb-8 animate-fade-in"
        >
          <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
          Tersedia 24 dokter aktif hari ini
        </div>

        <!-- Animated Headline -->
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 leading-tight mb-4 tracking-tight">
          Layanan kesehatan yang
          <span class="relative inline-flex justify-center overflow-hidden h-[1.2em] w-full mt-1">
            <transition-group name="slide" tag="span" class="relative w-full flex justify-center">
              <span
                v-for="(title, index) in titles"
                :key="title"
                v-show="titleNumber === index"
                class="absolute text-emerald-600 font-bold"
              >
                {{ title }}
              </span>
            </transition-group>
          </span>
        </h1>

        <p class="text-gray-500 text-lg leading-relaxed mb-10 max-w-2xl mx-auto">
          Booking antrian puskesmas & klinik terdekat tanpa antre panjang. Rekam medis tersimpan
          aman di satu tempat.
        </p>

        <!-- CTA Buttons -->
        <div class="flex items-center justify-center gap-3 mb-14 flex-wrap">
          <RouterLink
            to="/cari-dokter"
            class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-6 py-3 rounded-xl transition-all duration-200 text-sm shadow-lg shadow-emerald-200 hover:shadow-emerald-300 hover:-translate-y-0.5"
          >
            Cari Dokter Sekarang
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 8l4 4m0 0l-4 4m4-4H3"
              />
            </svg>
          </RouterLink>
          <a
            href="#cara-kerja"
            class="flex items-center gap-2 border border-gray-200 text-gray-600 hover:border-emerald-300 hover:text-emerald-600 font-medium px-6 py-3 rounded-xl transition-all duration-200 text-sm hover:-translate-y-0.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
              />
            </svg>
            Pelajari Lebih Lanjut
          </a>
        </div>

        <!-- Search Box -->
        <div
          class="bg-white border border-gray-200 rounded-2xl p-2 flex items-center gap-2 shadow-xl shadow-gray-100 max-w-xl mx-auto mb-14"
        >
          <div class="flex-1 flex items-center gap-2 px-3">
            <svg
              class="w-5 h-5 text-gray-400 flex-shrink-0"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Cari dokter atau spesialis..."
              class="flex-1 text-sm text-gray-700 placeholder:text-gray-400 outline-none bg-transparent py-1"
            />
          </div>
          <RouterLink
            to="/cari-dokter"
            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors whitespace-nowrap"
          >
            Cari
          </RouterLink>
        </div>

        <!-- Stats -->
        <div class="flex items-center justify-center gap-10 flex-wrap">
          <div v-for="stat in stats" :key="stat.label" class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ stat.value }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- LAYANAN -->
    <section id="layanan" class="py-20 px-6 bg-gray-50">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-2xl font-bold text-gray-900 mb-3">Layanan Kami</h2>
          <p class="text-gray-500 text-sm max-w-md mx-auto">
            Semua yang Anda butuhkan untuk mengakses layanan kesehatan lokal tersedia di satu
            platform.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <div
            v-for="layanan in layanans"
            :key="layanan.title"
            class="bg-white rounded-2xl p-6 border border-gray-100 hover:border-emerald-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer"
            @click="$router.push(layanan.link)"
          >
            <div
              class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
              :class="layanan.bgClass"
            >
              <span class="text-2xl">{{ layanan.icon }}</span>
            </div>
            <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ layanan.title }}</h3>
            <p class="text-xs text-gray-500 leading-relaxed">{{ layanan.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CARA KERJA -->
    <section id="cara-kerja" class="py-20 px-6 bg-white">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
          <h2 class="text-2xl font-bold text-gray-900 mb-3">Cara Kerja</h2>
          <p class="text-gray-500 text-sm">3 langkah mudah untuk dapatkan layanan kesehatan</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 relative">
          <!-- Connector line -->
          <div class="hidden md:block absolute top-7 left-1/4 right-1/4 h-px bg-emerald-100 z-0" />
          <div v-for="(step, i) in steps" :key="i" class="text-center relative z-10">
            <div
              class="w-14 h-14 bg-emerald-600 text-white rounded-2xl flex items-center justify-center text-xl font-bold mx-auto mb-5 shadow-lg shadow-emerald-200"
            >
              {{ i + 1 }}
            </div>
            <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ step.title }}</h3>
            <p class="text-xs text-gray-500 leading-relaxed max-w-xs mx-auto">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- DOKTER -->
    <section id="dokter" class="py-20 px-6 bg-gray-50">
      <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Dokter Tersedia Hari Ini</h2>
            <p class="text-sm text-gray-500">Booking langsung tanpa perlu antre di tempat</p>
          </div>
          <RouterLink
            to="/cari-dokter"
            class="text-sm text-emerald-600 font-medium hover:underline hidden md:block"
            >Lihat semua →</RouterLink
          >
        </div>

        <div class="flex gap-2 flex-wrap mb-8">
          <button
            v-for="f in filters"
            :key="f"
            @click="activeFilter = f"
            class="text-xs px-4 py-2 rounded-full border transition-all duration-200"
            :class="
              activeFilter === f
                ? 'bg-emerald-600 text-white border-emerald-600'
                : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-300'
            "
          >
            {{ f }}
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <!-- Loading skeleton -->
          <div v-if="loadingDokter" v-for="i in 6" :key="i"
            class="bg-white rounded-2xl p-5 border border-gray-100 animate-pulse">
            <div class="flex gap-4 mb-4">
              <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
            <div class="h-8 bg-gray-200 rounded-xl"></div>
          </div>
          <!-- Data dokter dari API -->
          <div v-else
            v-for="dokter in filteredDokter"
            :key="dokter.nama"
            class="bg-white rounded-2xl p-5 border border-gray-100 hover:border-emerald-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
          >
            <div class="flex items-start gap-4 mb-4">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0"
                :style="{ background: dokter.avatarBg, color: dokter.avatarColor }"
              >
                {{ dokter.inisial }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-gray-800 truncate">{{ dokter.nama }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ dokter.spesialis }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ dokter.faskes }}</div>
              </div>
              <span
                class="text-xs px-2 py-1 rounded-full flex-shrink-0"
                :class="
                  dokter.tersedia ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'
                "
              >
                {{ dokter.tersedia ? 'Tersedia' : 'Penuh' }}
              </span>
            </div>
            <div class="flex items-center gap-3 mb-4 flex-wrap">
              <span class="text-xs text-gray-400">⭐ {{ dokter.rating }}</span>
              <span class="text-gray-200">|</span>
              <span class="text-xs text-gray-400">{{ dokter.jadwal }}</span>
              <span class="text-gray-200">|</span>
              <span class="text-xs text-gray-400">Sisa {{ dokter.kuota }} kuota</span>
            </div>
            <button
              :disabled="!dokter.tersedia"
              @click="bookingDokter(dokter)"
              class="w-full py-2.5 rounded-xl text-xs font-medium transition-all duration-200"
              :class="
                dokter.tersedia
                  ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                  : 'bg-gray-100 text-gray-400 cursor-not-allowed'
              "
            >
              {{ dokter.tersedia ? 'Booking Antrian' : 'Kuota Habis' }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-400 py-12 px-6">
      <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row items-start justify-between gap-8 mb-10">
          <div>
            <div class="flex items-center gap-2 mb-3">
              <div class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center">
                <img src="/logo.png" alt="SeuramoeSihat" class="w-full h-full object-contain" />
              </div>
              <span class="text-white font-semibold">SeuramoeSihat</span>
            </div>
            <p class="text-xs text-gray-500 max-w-xs leading-relaxed">
              Platform booking dokter lokal untuk wilayah Aceh dan sekitarnya.
            </p>
          </div>
          <div class="grid grid-cols-2 gap-8">
            <div>
              <div class="text-white font-medium mb-3 text-xs">Layanan</div>
              <div class="space-y-2 text-xs">
                <div class="hover:text-white transition-colors cursor-pointer">Antrian Online</div>
                <div class="hover:text-white transition-colors cursor-pointer">Konsultasi Chat</div>
                <div class="hover:text-white transition-colors cursor-pointer">Rekam Medis</div>
              </div>
            </div>
            <div>
              <div class="text-white font-medium mb-3 text-xs">Informasi</div>
              <div class="space-y-2 text-xs">
                <div class="hover:text-white transition-colors cursor-pointer">Tentang Kami</div>
                <div class="hover:text-white transition-colors cursor-pointer">Kontak</div>
                <div class="hover:text-white transition-colors cursor-pointer">
                  Kebijakan Privasi
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="border-t border-gray-800 pt-6 text-center text-xs text-gray-600">
          © 2026 SeuramoeSihat. Semua hak dilindungi.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api/axios'
import { useAuthStore } from '../stores/auth'

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
const search = ref('')
const titleNumber = ref(0)
const titles = [
  'mudah diakses',
  'cepat & efisien',
  'terpercaya',
  'dekat dari rumah',
  'tanpa antre panjang',
]

let interval = null
onMounted(() => {
  interval = setInterval(() => {
    titleNumber.value = (titleNumber.value + 1) % titles.length
  }, 2000)
  loadDokter()
})
onUnmounted(() => clearInterval(interval))

const stats = [
  { value: '248+', label: 'Dokter aktif' },
  { value: '62', label: 'Faskes terdaftar' },
  { value: '12rb+', label: 'Pasien terbantu' },
]

const layanans = [
  { icon: '📋', title: 'Antrian Online',   desc: 'Ambil nomor antrian dari rumah tanpa perlu datang lebih awal.',          link: '/antrian',    bgClass: 'bg-emerald-50' },
  { icon: '💬', title: 'Konsultasi Chat',  desc: 'Tanya keluhan ringan langsung ke dokter via pesan teks.',                link: '/konsultasi', bgClass: 'bg-blue-50'    },
  { icon: '📄', title: 'Rekam Medis',      desc: 'Riwayat pemeriksaan tersimpan digital dan bisa diakses kapan saja.',     link: '/rekam-medis',bgClass: 'bg-purple-50'  },
  { icon: '👤', title: 'Profil Saya',      desc: 'Kelola data diri dan informasi kesehatan Anda dalam satu tempat yang mudah diakses.',  link: '/profil',     bgClass: 'bg-teal-50'    },
]

const steps = [
  { title: 'Cari dokter atau faskes',  desc: 'Temukan dokter terdekat berdasarkan spesialisasi dan ketersediaan jadwal hari ini.' },
  { title: 'Booking antrian online',   desc: 'Pilih jadwal dan ambil nomor antrian dari mana saja tanpa harus antre fisik.' },
  { title: 'Datang & diperiksa',       desc: 'Pantau status antrian real-time dan datang saat giliran Anda hampir tiba.' },
]

const filters = ['Semua', 'Dokter Umum', 'Spesialis Anak', 'Gigi', 'Kandungan']
const activeFilter = ref('Semua')

// Data dokter dari API
const dokters = ref([])
const loadingDokter = ref(false)

async function loadDokter() {
  loadingDokter.value = true
  try {
    const res = await api.get('/dokter')
    dokters.value = res.data.data.map((d) => ({
      id:          d.id,
      inisial:     d.inisial,
      nama:        d.nama,
      spesialis:   d.spesialis,
      faskes:      d.faskes,
      jadwal:      d.jadwal,
      kuota:       d.kuota,
      rating:      d.rating,
      tersedia:    d.tersedia,
      kategori:    d.kategori,
      avatarBg:    d.avatar_bg,
      avatarColor: d.avatar_color,
    }))
  } catch {
    // fallback diam jika gagal
  } finally {
    loadingDokter.value = false
  }
}

const filteredDokter = computed(() => {
  if (activeFilter.value === 'Semua') return dokters.value
  return dokters.value.filter((d) => d.kategori === activeFilter.value)
})

function bookingDokter(dokter) {
  router.push('/booking/' + dokter.id)
}
</script>

<style scoped>
.slide-enter-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-enter-from {
  opacity: 0;
  transform: translateY(60px);
}
.slide-leave-to {
  opacity: 0;
  transform: translateY(-60px);
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}
</style>
