<template>
  <div class="min-h-screen bg-gray-50 pb-24">
    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
        <RouterLink to="/" class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center">
            <img src="/logo.png" alt="SeuramoeSihat" class="w-full h-full object-contain" />
          </div>
          <span class="text-lg font-semibold text-gray-800">SeuramoeSihat</span>
        </RouterLink>
        <RouterLink to="/cari-dokter" class="text-sm font-medium text-emerald-600">+ Booking Baru</RouterLink>
      </div>
    </nav>

    <div class="max-w-3xl mx-auto px-6 py-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Antrian Saya</h1>

      <!-- Tab dengan badge count -->
      <div class="flex bg-gray-100 rounded-xl p-1 mb-6">
        <button
          v-for="tab in tabs" :key="tab.key"
          @click="activeTab = tab.key; onTabChange()"
          class="flex-1 py-2 text-xs font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2"
          :class="activeTab === tab.key ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500'"
        >
          {{ tab.label }}
          <span
            v-if="tab.key === 'aktif' && store.aktifList.length > 0"
            class="bg-emerald-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold leading-none"
          >{{ store.aktifList.length }}</span>
        </button>
      </div>

      <!-- Loading skeleton -->
      <div v-if="store.loading" class="space-y-6">
        <div v-for="i in 2" :key="i" class="rounded-2xl overflow-hidden animate-pulse">
          <div class="bg-gray-200 h-14 rounded-2xl mb-1"></div>
          <div class="bg-white rounded-2xl p-5 space-y-3 border border-gray-100">
            <div class="h-5 bg-gray-200 rounded w-1/2"></div>
            <div class="h-3 bg-gray-200 rounded w-1/3"></div>
            <div class="h-16 bg-gray-100 rounded-xl"></div>
          </div>
        </div>
      </div>

      <!-- AKTIF -->
      <div v-else-if="activeTab === 'aktif'">

        <!-- Kosong -->
        <div v-if="store.aktifList.length === 0" class="text-center py-16">
          <div class="text-5xl mb-4">📋</div>
          <p class="text-gray-500 text-sm font-medium">Tidak ada antrian aktif</p>
          <p class="text-gray-400 text-xs mt-1 mb-6">Booking antrian dokter sekarang</p>
          <RouterLink
            to="/cari-dokter"
            class="inline-block bg-emerald-600 text-white text-sm font-medium px-6 py-3 rounded-xl hover:bg-emerald-700 transition-colors"
          >Cari Dokter</RouterLink>
        </div>

        <!-- Daftar antrian — satu grup per antrian (ticker + card) -->
        <div v-else class="space-y-6">
          <div v-for="a in store.aktifList" :key="a.id">

            <!-- Live ticker — tiap antrian punya sendiri -->
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl px-5 py-3 flex items-center gap-3 mb-2">
              <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse flex-shrink-0"></span>
              <p class="text-sm text-emerald-800">
                Sedang memanggil nomor <strong>{{ liveOf(a).nomor_dipanggil }}</strong> —
                Anda nomor <strong>{{ a.nomor_antrian }}</strong>,
                sisa <strong>{{ liveOf(a).sisa_antrian }}</strong> orang.
              </p>
            </div>

            <!-- Card antrian -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">

              <!-- Header hijau untuk semua antrian aktif -->
              <div class="bg-emerald-600 px-6 py-5 flex items-center justify-between">
                <div>
                  <p class="text-emerald-100 text-xs mb-1">
                    {{ isHariIni(a.tanggal_raw) ? 'Antrian aktif hari ini' : 'Antrian mendatang — ' + a.tanggal }}
                  </p>
                  <p class="text-white text-lg font-bold">{{ a.faskes }}</p>
                  <p class="text-emerald-200 text-xs mt-0.5">
                    {{ a.dokter?.nama }} — {{ a.dokter?.spesialis }}
                  </p>
                </div>
                <div class="bg-white rounded-xl px-4 py-3 text-center min-w-[72px]">
                  <p class="text-xs text-emerald-600 font-medium">Nomor Anda</p>
                  <p class="text-3xl font-bold text-emerald-700 leading-tight">{{ a.nomor_antrian }}</p>
                </div>
              </div>

              <div class="p-6">
                <!-- Progress -->
                <div class="flex justify-between text-xs text-gray-500 mb-3">
                  <span>Dipanggil: <strong class="text-gray-800">{{ liveOf(a).nomor_dipanggil }}</strong></span>
                  <span>Sisa {{ liveOf(a).sisa_antrian }} orang</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-5">
                  <div
                    class="h-full bg-emerald-500 rounded-full transition-all duration-700"
                    :style="{ width: progressPersen(a) + '%' }"
                  ></div>
                </div>

                <!-- Stats 3 kolom -->
                <div class="grid grid-cols-3 gap-3 mb-5">
                  <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-sm font-bold text-gray-800">~{{ liveOf(a).estimasi_menit }} mnt</p>
                    <p class="text-xs text-gray-400 mt-0.5">Estimasi</p>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-sm font-bold text-gray-800">{{ a.jam }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Jam daftar</p>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-sm font-bold text-gray-800">{{ a.tipe_pasien === 'Pasien Baru' ? 'Baru' : 'Lama' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Tipe pasien</p>
                  </div>
                </div>

                <!-- Keluhan -->
                <div class="bg-gray-50 rounded-xl p-4 mb-5">
                  <p class="text-xs font-medium text-gray-700 mb-1">Keluhan yang dilaporkan</p>
                  <p class="text-xs text-gray-500 leading-relaxed">{{ a.keluhan }}</p>
                </div>

                <!-- Aksi -->
                <div class="flex gap-3">
                  <button
                    @click="batalkan(a.id)"
                    class="flex-1 py-2.5 text-xs font-medium border border-red-300 text-red-500 rounded-xl hover:bg-red-50 transition-colors"
                  >Batalkan</button>
                  <button
                    class="flex-[2] py-2.5 text-xs font-medium bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2"
                  >
                    <span>📱</span> Notif WhatsApp
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIWAYAT -->
      <div v-else-if="activeTab === 'riwayat'" class="space-y-4">
        <div v-if="store.riwayat.length === 0" class="text-center py-16">
          <div class="text-4xl mb-3">📋</div>
          <p class="text-gray-500 text-sm">Belum ada riwayat antrian</p>
        </div>
        <div
          v-for="r in store.riwayat" :key="r.id"
          class="bg-white rounded-2xl p-5 border border-gray-100 hover:border-emerald-200 transition-colors"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <p class="text-sm font-semibold text-gray-800">{{ r.faskes }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ r.dokter?.nama }}</p>
            </div>
            <span
              class="text-xs px-2 py-1 rounded-full"
              :class="r.status === 'selesai' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'"
            >{{ r.status === 'selesai' ? 'Selesai' : 'Dibatalkan' }}</span>
          </div>
          <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
            <span>📅 {{ r.tanggal }}</span>
            <span>•</span>
            <span>🔢 No. {{ r.nomor_antrian }}</span>
          </div>
          <div v-if="r.rekam_medis?.diagnosa" class="bg-gray-50 rounded-xl px-4 py-3 mb-3">
            <p class="text-xs text-gray-500">
              <span class="font-medium text-gray-700">Diagnosa:</span> {{ r.rekam_medis.diagnosa }}
            </p>
          </div>
          <button
            @click="$router.push('/cari-dokter')"
            class="w-full py-2 text-xs font-medium border border-emerald-200 text-emerald-600 rounded-xl hover:bg-emerald-50 transition-colors"
          >🔄 Booking Ulang</button>
        </div>
      </div>
    </div>

    <BottomNav active="antrian" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAntrianStore } from '../stores/antrian'
import BottomNav from '../components/BottomNav.vue'

const store = useAntrianStore()
const activeTab = ref('aktif')
const tabs = [
  { key: 'aktif',   label: 'Aktif'   },
  { key: 'riwayat', label: 'Riwayat' },
]

// statusLive: { [antrianId]: { nomor_dipanggil, sisa_antrian, estimasi_menit } }
const statusLive = ref({})
const today = new Date().toISOString().split('T')[0]

function isHariIni(tanggalRaw) {
  return tanggalRaw === today
}

// Helper — kembalikan live data antrian, default 0 jika belum ada
function liveOf(a) {
  return statusLive.value[a.id] ?? { nomor_dipanggil: 0, sisa_antrian: 0, estimasi_menit: 0 }
}

function progressPersen(a) {
  const total     = a.nomor_antrian
  const dipanggil = liveOf(a).nomor_dipanggil
  return total > 0 ? Math.min(100, Math.round((dipanggil / total) * 100)) : 0
}

// Poll status untuk SEMUA antrian aktif (bukan hanya hari ini)
async function pollStatus() {
  for (const a of store.aktifList) {
    const s = await store.fetchStatus(a.id)
    if (s) statusLive.value = { ...statusLive.value, [a.id]: s }
  }
}

function onTabChange() {
  if (activeTab.value === 'riwayat') store.fetchRiwayat()
}

async function batalkan(id) {
  if (confirm('Yakin ingin membatalkan antrian ini?')) {
    await store.batalkan(id)
  }
}

let pollInterval = null
onMounted(async () => {
  await store.fetchAktif()
  await pollStatus()
  pollInterval = setInterval(pollStatus, 10000)
})
onUnmounted(() => clearInterval(pollInterval))
</script>
