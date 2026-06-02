<template>
  <div class="min-h-screen bg-gray-50 pb-24">
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-3xl mx-auto px-6 py-4 flex items-center gap-4">
        <RouterLink to="/" class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center">
            <img src="/logo.png" alt="SeuramoeSihat" class="w-full h-full object-contain" />
          </div>
          <span class="text-lg font-semibold text-gray-800">SeuramoeSihat</span>
        </RouterLink>
      </div>
    </nav>

    <div class="max-w-3xl mx-auto px-6 py-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Rekam Medis</h1>
      <p class="text-sm text-gray-500 mb-6">Riwayat kesehatan digital Anda tersimpan aman</p>

      <!-- Info Card -->
      <div class="bg-white rounded-2xl p-5 border border-gray-100 mb-6">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">👤</div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-gray-800">{{ auth.namaUser }}</p>
            <p class="text-xs text-gray-500 mt-0.5">NIK: {{ auth.user?.nik || '-' }}</p>
            <div class="flex gap-2 mt-2 flex-wrap">
              <span class="text-xs bg-red-50 text-red-600 px-2 py-1 rounded-full">
                Gol. Darah: {{ auth.user?.profil_kesehatan?.golongan_darah || '-' }}
              </span>
              <span class="text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded-full">
                Alergi: {{ auth.user?.profil_kesehatan?.alergi || 'Tidak ada' }}
              </span>
              <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                Berat: {{ auth.user?.profil_kesehatan?.berat_badan ? auth.user.profil_kesehatan.berat_badan + ' kg' : '-' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 text-center">
          <p class="text-2xl font-bold text-emerald-600">{{ stats.total_kunjungan }}</p>
          <p class="text-xs text-gray-400 mt-1">Total kunjungan</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 text-center">
          <p class="text-2xl font-bold text-blue-600">{{ stats.dokter_berbeda }}</p>
          <p class="text-xs text-gray-400 mt-1">Dokter berbeda</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 text-center">
          <p class="text-2xl font-bold text-purple-600">{{ stats.faskes_dikunjungi }}</p>
          <p class="text-xs text-gray-400 mt-1">Faskes dikunjungi</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="bg-white rounded-2xl p-5 border border-gray-100 animate-pulse">
          <div class="flex justify-between mb-3">
            <div class="space-y-2">
              <div class="h-4 bg-gray-200 rounded w-40"></div>
              <div class="h-3 bg-gray-200 rounded w-28"></div>
            </div>
            <div class="h-3 bg-gray-200 rounded w-20"></div>
          </div>
          <div class="h-10 bg-gray-100 rounded-xl"></div>
        </div>
      </div>

      <!-- Riwayat -->
      <h2 v-if="!loading" class="text-sm font-semibold text-gray-800 mb-4">Riwayat Kunjungan</h2>
      <div v-if="!loading" class="space-y-4">
        <div v-for="r in rekamMedis" :key="r.id"
          class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-emerald-200 hover:shadow-sm transition-all duration-200 cursor-pointer"
          @click="r.expanded = !r.expanded">
          <div class="p-5">
            <div class="flex items-start justify-between mb-2">
              <div>
                <p class="text-sm font-semibold text-gray-800">{{ r.faskes }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ r.dokter }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-gray-400">{{ r.tanggal }}</p>
                <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full mt-1 inline-block">Selesai</span>
              </div>
            </div>
            <div class="bg-gray-50 rounded-xl px-4 py-3">
              <p class="text-xs text-gray-600"><span class="font-medium">Keluhan:</span> {{ r.keluhan }}</p>
            </div>
          </div>

          <!-- Expanded -->
          <div v-if="r.expanded" class="border-t border-gray-50 px-5 pb-5 space-y-3">
            <div class="bg-emerald-50 rounded-xl px-4 py-3">
              <p class="text-xs text-gray-600">
                <span class="font-medium text-emerald-800">Diagnosa:</span> {{ r.diagnosa }}
              </p>
            </div>
            <div class="bg-blue-50 rounded-xl px-4 py-3">
              <p class="text-xs font-medium text-blue-800 mb-1">Resep Obat</p>
              <ul class="space-y-1">
                <li v-for="obat in r.resep" :key="obat" class="text-xs text-blue-700 flex items-center gap-1">
                  <span>💊</span> {{ obat }}
                </li>
              </ul>
            </div>
            <div v-if="r.catatan" class="bg-gray-50 rounded-xl px-4 py-3">
              <p class="text-xs text-gray-600">
                <span class="font-medium">Catatan dokter:</span> {{ r.catatan }}
              </p>
            </div>
          </div>

          <div class="px-5 pb-4">
            <p class="text-xs text-emerald-600 text-center">
              {{ r.expanded ? '▲ Sembunyikan detail' : '▼ Lihat detail lengkap' }}
            </p>
          </div>
        </div>

        <div v-if="rekamMedis.length === 0" class="text-center py-16">
          <div class="text-4xl mb-3">📄</div>
          <p class="text-gray-500 text-sm font-medium mb-1">Belum ada rekam medis</p>
          <p class="text-gray-400 text-xs mb-5">Rekam medis akan muncul setelah kamu melakukan kunjungan ke dokter melalui SeuramoeSihat.</p>
          <RouterLink
            to="/cari-dokter"
            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors"
          >
            Cari Dokter Sekarang
          </RouterLink>
        </div>
      </div>
    </div>

    <BottomNav active="rekam" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'
import BottomNav from '../components/BottomNav.vue'

const auth = useAuthStore()
const loading = ref(true)
const rekamMedis = reactive([])
const stats = reactive({ total_kunjungan: 0, dokter_berbeda: 0, faskes_dikunjungi: 0 })

onMounted(async () => {
  try {
    const res = await api.get('/rekam-medis')
    Object.assign(stats, res.data.stats)
    res.data.data.forEach((r) => rekamMedis.push({ ...r, expanded: false }))
  } catch {
    // abaikan
  } finally {
    loading.value = false
  }
})
</script>
