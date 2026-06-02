<template>
  <div class="min-h-screen bg-gray-50 pb-10">
    <!-- NAVBAR -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-3xl mx-auto px-6 py-4 flex items-center gap-4">
        <button @click="$router.back()"
          class="w-9 h-9 border border-gray-200 rounded-xl flex items-center justify-center hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <span class="text-sm font-semibold text-gray-800">Booking Antrian</span>
        <div class="ml-auto flex items-center gap-1.5">
          <div v-for="i in 3" :key="i" class="h-1.5 rounded-full transition-all duration-300"
            :class="[step >= i ? 'bg-emerald-600' : 'bg-gray-200', step === i ? 'w-6' : 'w-3']" />
        </div>
      </div>
    </nav>

    <!-- Loading awal -->
    <div v-if="loadingInit" class="flex items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else class="max-w-3xl mx-auto px-6 py-8">

      <!-- ===== STEP 1: Pilih Dokter & Jadwal ===== -->
      <div v-if="step === 1">
        <h1 class="text-xl font-bold text-gray-900 mb-1">Pilih Dokter</h1>
        <p class="text-sm text-gray-500 mb-6">Pilih dokter dan jadwal yang tersedia</p>

        <!-- Info dokter terpilih -->
        <div v-if="dokterDipilih" class="bg-white rounded-2xl p-5 border border-emerald-200 mb-6 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0"
              :style="{ background: dokterDipilih.avatarBg, color: dokterDipilih.avatarColor }">
              {{ dokterDipilih.inisial }}
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-gray-800">{{ dokterDipilih.nama }}</p>
              <p class="text-xs text-gray-500">{{ dokterDipilih.spesialis }} — {{ dokterDipilih.faskes }}</p>
              <div class="flex gap-2 mt-2 flex-wrap">
                <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Tersedia</span>
                <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full">⭐ {{ dokterDipilih.rating }}</span>
                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">Sisa {{ dokterDipilih.kuota }} kuota</span>
              </div>
            </div>
            <button @click="dokterDipilih = null; jadwalList = []; tanggalDipilih = ''; jamDipilih = ''"
              class="text-xs text-gray-400 hover:text-red-500 transition-colors">Ganti</button>
          </div>
        </div>

        <!-- Pilih dokter jika belum -->
        <div v-else class="space-y-3 mb-6">
          <p class="text-xs font-medium text-gray-600 mb-2">Pilih dokter:</p>
          <div v-if="loadingDokter" class="text-center py-8">
            <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
          </div>
          <div v-else v-for="d in dokterList" :key="d.id" @click="d.tersedia && pilihDokter(d)"
            class="bg-white rounded-2xl p-4 border border-gray-100 hover:border-emerald-300 hover:shadow-sm transition-all duration-200 flex items-center gap-4"
            :class="d.tersedia ? 'cursor-pointer' : 'opacity-50 cursor-not-allowed'">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
              :style="{ background: d.avatarBg, color: d.avatarColor }">{{ d.inisial }}</div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-800 truncate">{{ d.nama }}</p>
              <p class="text-xs text-gray-500">{{ d.spesialis }} — {{ d.faskes }}</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
              :class="d.tersedia ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'">
              {{ d.tersedia ? 'Tersedia' : 'Penuh' }}
            </span>
          </div>
        </div>

        <!-- Pilih Tanggal -->
        <div v-if="dokterDipilih" class="bg-white rounded-2xl p-5 border border-gray-100 mb-5">
          <p class="text-sm font-semibold text-gray-800 mb-4">Pilih Tanggal</p>
          <div v-if="loadingJadwal" class="text-center py-4">
            <div class="w-5 h-5 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
          </div>
          <div v-else-if="jadwalList.length === 0" class="text-xs text-gray-400 text-center py-4">Tidak ada jadwal tersedia</div>
          <div v-else class="flex gap-2 overflow-x-auto pb-1">
            <button v-for="tgl in jadwalList" :key="tgl.tanggal"
              @click="tanggalDipilih = tgl.tanggal; jamDipilih = ''"
              class="flex-shrink-0 flex flex-col items-center px-4 py-3 rounded-xl border text-xs transition-all duration-200 min-w-[60px]"
              :class="tanggalDipilih === tgl.tanggal ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-300'">
              <span class="font-medium">{{ tgl.hari }}</span>
              <span class="text-lg font-bold mt-0.5">{{ tgl.tanggal_angka }}</span>
              <span class="opacity-70">{{ tgl.bulan }}</span>
            </button>
          </div>
        </div>

        <!-- Pilih Jam -->
        <div v-if="dokterDipilih && tanggalDipilih" class="bg-white rounded-2xl p-5 border border-gray-100 mb-6">
          <p class="text-sm font-semibold text-gray-800 mb-4">Pilih Jam</p>
          <div v-if="slotHariIni.length === 0" class="text-xs text-gray-400 text-center py-4">Tidak ada slot di tanggal ini</div>
          <div v-else class="grid grid-cols-4 gap-2">
            <button v-for="slot in slotHariIni" :key="slot.id"
              @click="slot.tersedia && (jamDipilih = slot.jam)"
              class="py-2.5 rounded-xl text-xs font-medium border transition-all duration-200"
              :class="[
                !slot.tersedia ? 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed' :
                jamDipilih === slot.jam ? 'bg-emerald-600 text-white border-emerald-600' :
                'bg-white text-gray-600 border-gray-200 hover:border-emerald-300'
              ]">
              {{ slot.jam }}
            </button>
          </div>
        </div>

        <button @click="keStep2" :disabled="!dokterDipilih || !tanggalDipilih || !jamDipilih"
          class="w-full py-3.5 rounded-xl text-sm font-semibold transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-200">
          Lanjut → Isi Data Keluhan
        </button>
      </div>

      <!-- ===== STEP 2: Isi Keluhan ===== -->
      <div v-if="step === 2">
        <h1 class="text-xl font-bold text-gray-900 mb-1">Isi Data Keluhan</h1>
        <p class="text-sm text-gray-500 mb-6">Ceritakan keluhan Anda agar dokter bisa mempersiapkan pemeriksaan</p>

        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 mb-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
              :style="{ background: dokterDipilih.avatarBg, color: dokterDipilih.avatarColor }">
              {{ dokterDipilih.inisial }}
            </div>
            <div>
              <p class="text-sm font-semibold text-emerald-900">{{ dokterDipilih.nama }}</p>
              <p class="text-xs text-emerald-700">{{ tanggalDipilih }} • {{ jamDipilih }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 mb-5 space-y-5">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-2">Nama Pasien <span class="text-red-500">*</span></label>
            <input v-model="form.nama" type="text" placeholder="Nama lengkap pasien"
              class="w-full h-10 border border-gray-200 rounded-xl px-4 text-sm text-gray-700 placeholder:text-gray-400 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-2">Nomor HP <span class="text-red-500">*</span></label>
            <input v-model="form.noHp" type="tel" placeholder="08xxxxxxxxxx"
              class="w-full h-10 border border-gray-200 rounded-xl px-4 text-sm text-gray-700 placeholder:text-gray-400 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-2">Keluhan Utama <span class="text-red-500">*</span></label>
            <textarea v-model="form.keluhan" rows="3" placeholder="Contoh: Demam 2 hari, batuk kering, sakit kepala..."
              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 placeholder:text-gray-400 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all resize-none" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-2">Riwayat Alergi Obat</label>
            <input v-model="form.alergi" type="text" placeholder="Kosongkan jika tidak ada"
              class="w-full h-10 border border-gray-200 rounded-xl px-4 text-sm text-gray-700 placeholder:text-gray-400 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-3">Tipe Pasien</label>
            <div class="flex gap-3">
              <button v-for="tipe in ['Pasien Baru', 'Pasien Lama']" :key="tipe" type="button"
                @click="form.tipePasien = tipe"
                class="flex-1 py-2.5 rounded-xl text-xs font-medium border transition-all duration-200"
                :class="form.tipePasien === tipe ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-300'">
                {{ tipe }}
              </button>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="step = 1" class="flex-1 py-3 rounded-xl text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">← Kembali</button>
          <button @click="keStep3" :disabled="!form.nama || !form.noHp || !form.keluhan"
            class="flex-2 px-8 py-3 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-emerald-200">
            Lanjut → Konfirmasi
          </button>
        </div>
      </div>

      <!-- ===== STEP 3: Konfirmasi ===== -->
      <div v-if="step === 3">
        <h1 class="text-xl font-bold text-gray-900 mb-1">Konfirmasi Booking</h1>
        <p class="text-sm text-gray-500 mb-6">Periksa kembali detail booking Anda</p>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-4">
          <div class="bg-emerald-600 px-5 py-4">
            <p class="text-emerald-100 text-xs mb-1">Dokter yang dipilih</p>
            <p class="text-white font-bold">{{ dokterDipilih.nama }}</p>
            <p class="text-emerald-200 text-xs mt-0.5">{{ dokterDipilih.spesialis }} — {{ dokterDipilih.faskes }}</p>
          </div>
          <div class="p-5 grid grid-cols-2 gap-4">
            <div><p class="text-xs text-gray-400 mb-1">Tanggal</p><p class="text-sm font-semibold text-gray-800">{{ tanggalDipilih }}</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Jam</p><p class="text-sm font-semibold text-gray-800">{{ jamDipilih }}</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Estimasi Nomor</p><p class="text-sm font-semibold text-gray-800">#{{ nomorEstimasi }}</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Status</p><span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Tersedia</span></div>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 mb-4">
          <p class="text-sm font-semibold text-gray-800 mb-4">Data Pasien</p>
          <div class="space-y-3">
            <div class="flex items-center justify-between"><span class="text-xs text-gray-400">Nama</span><span class="text-sm font-medium text-gray-800">{{ form.nama }}</span></div>
            <div class="flex items-center justify-between"><span class="text-xs text-gray-400">Nomor HP</span><span class="text-sm font-medium text-gray-800">{{ form.noHp }}</span></div>
            <div class="flex items-start justify-between gap-4"><span class="text-xs text-gray-400 flex-shrink-0">Keluhan</span><span class="text-sm text-gray-800 text-right">{{ form.keluhan }}</span></div>
            <div v-if="form.alergi" class="flex items-center justify-between"><span class="text-xs text-gray-400">Alergi</span><span class="text-sm font-medium text-gray-800">{{ form.alergi }}</span></div>
            <div class="flex items-center justify-between"><span class="text-xs text-gray-400">Tipe Pasien</span><span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ form.tipePasien }}</span></div>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-100 mb-6">
          <label class="flex items-center gap-3 cursor-pointer" @click="form.notifWa = !form.notifWa">
            <div class="w-10 h-6 rounded-full transition-colors duration-200 relative flex-shrink-0" :class="form.notifWa ? 'bg-emerald-600' : 'bg-gray-200'">
              <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all duration-200" :class="form.notifWa ? 'left-5' : 'left-1'" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-800">Notifikasi WhatsApp</p>
              <p class="text-xs text-gray-400">Dapatkan update antrian via WhatsApp</p>
            </div>
          </label>
        </div>

        <div class="flex gap-3">
          <button @click="step = 2" class="flex-1 py-3 rounded-xl text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">← Kembali</button>
          <button @click="konfirmasiBooking" :disabled="loadingBooking"
            class="flex-2 px-8 py-3 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors disabled:opacity-50 shadow-lg shadow-emerald-200 flex items-center gap-2">
            <span v-if="loadingBooking" class="w-4 h-4 border-2 border-white/50 border-t-transparent rounded-full animate-spin" />
            <span v-else>✅ Konfirmasi Booking</span>
          </button>
        </div>
        <p v-if="errorBooking" class="text-xs text-red-500 text-center mt-3">{{ errorBooking }}</p>
      </div>

      <!-- ===== STEP 4: Sukses ===== -->
      <div v-if="step === 4" class="text-center py-10">
        <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">✅</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Booking Berhasil!</h1>
        <p class="text-gray-500 text-sm mb-8 max-w-xs mx-auto">Antrian Anda sudah terdaftar. Pantau status antrian secara real-time.</p>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-8 text-left shadow-sm">
          <div class="bg-emerald-600 px-6 py-5 flex items-center justify-between">
            <div>
              <p class="text-emerald-100 text-xs mb-1">SeuramoeSihat</p>
              <p class="text-white font-bold text-lg">{{ dokterDipilih.faskes }}</p>
              <p class="text-emerald-200 text-xs mt-0.5">{{ dokterDipilih.nama }}</p>
            </div>
            <div class="bg-white rounded-xl px-5 py-3 text-center">
              <p class="text-xs text-emerald-600">Nomor Antrian</p>
              <p class="text-4xl font-bold text-emerald-700">#{{ nomorEstimasi }}</p>
            </div>
          </div>
          <div class="p-5 grid grid-cols-2 gap-4">
            <div><p class="text-xs text-gray-400">Tanggal</p><p class="text-sm font-semibold text-gray-800 mt-0.5">{{ tanggalDipilih }}</p></div>
            <div><p class="text-xs text-gray-400">Jam</p><p class="text-sm font-semibold text-gray-800 mt-0.5">{{ jamDipilih }}</p></div>
            <div><p class="text-xs text-gray-400">Pasien</p><p class="text-sm font-semibold text-gray-800 mt-0.5">{{ form.nama }}</p></div>
            <div><p class="text-xs text-gray-400">Status</p><span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full mt-0.5 inline-block">Terdaftar</span></div>
          </div>
        </div>

        <div class="flex gap-3">
          <RouterLink to="/antrian" class="flex-1 py-3 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors text-center shadow-lg shadow-emerald-200">Pantau Antrian</RouterLink>
          <RouterLink to="/" class="flex-1 py-3 rounded-xl text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors text-center">Kembali ke Beranda</RouterLink>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDokterStore } from '../stores/dokter'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'

const route = useRoute()
const router = useRouter()
const dokterStore = useDokterStore()
const auth = useAuthStore()

const step = ref(1)
const loadingInit = ref(true)
const loadingDokter = ref(false)
const loadingJadwal = ref(false)
const loadingBooking = ref(false)
const errorBooking = ref('')
const hasilBooking = ref(null)

const dokterList = ref([])
const dokterDipilih = ref(null)
const jadwalList = ref([])
const tanggalDipilih = ref('')
const jamDipilih = ref('')

// Form — selalu bisa diinput, diisi dari user jika ada
const form = ref({
  nama: '',
  noHp: '',
  keluhan: '',
  alergi: '',
  tipePasien: 'Pasien Baru',
  notifWa: true,
})

const slotHariIni = computed(() => {
  const hari = jadwalList.value.find((j) => j.tanggal === tanggalDipilih.value)
  return hari?.slot ?? []
})

const nomorEstimasi = computed(() => hasilBooking.value?.nomor_antrian ?? '-')

onMounted(async () => {
  // Pastikan user sudah login — jika tidak, redirect ke login
  if (!auth.token) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }

  // Fetch data user terbaru jika belum ada
  if (!auth.user) {
    await auth.fetchMe()
  }

  // Jika setelah fetchMe masih tidak ada user (token invalid)
  if (!auth.user) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }

  // Isi form dari data user
  form.value.nama  = auth.user.nama  || ''
  form.value.noHp  = auth.user.no_hp || ''
  form.value.alergi = auth.user.profil_kesehatan?.alergi || ''

  // Load daftar dokter
  loadingDokter.value = true
  await dokterStore.fetchList()
  loadingDokter.value = false

  dokterList.value = dokterStore.list.map((d) => ({
    id:          d.id,
    inisial:     d.inisial,
    nama:        d.nama,
    spesialis:   d.spesialis,
    faskes:      d.faskes,
    kuota:       d.kuota,
    rating:      d.rating,
    tersedia:    d.tersedia,
    avatarBg:    d.avatar_bg,
    avatarColor: d.avatar_color,
  }))

  // Auto-pilih dokter dari route param
  const idParam = Number(route.params.id)
  if (idParam) {
    const found = dokterList.value.find((d) => d.id === idParam)
    if (found) await pilihDokter(found)
  }

  loadingInit.value = false
})

async function pilihDokter(d) {
  dokterDipilih.value = d
  loadingJadwal.value = true
  await dokterStore.fetchJadwal(d.id)
  loadingJadwal.value = false
  jadwalList.value = dokterStore.jadwal

  // Pilih tanggal pertama yang ada slot tersedia
  const pertama = jadwalList.value.find((j) => j.slot.some((s) => s.tersedia))
  if (pertama) tanggalDipilih.value = pertama.tanggal
}

function keStep2() {
  if (!dokterDipilih.value || !tanggalDipilih.value || !jamDipilih.value) return
  step.value = 2
  window.scrollTo(0, 0)
}

function keStep3() {
  if (!form.value.nama || !form.value.noHp || !form.value.keluhan) return
  step.value = 3
  window.scrollTo(0, 0)
}

async function konfirmasiBooking() {
  loadingBooking.value = true
  errorBooking.value = ''
  try {
    const res = await api.post('/booking', {
      dokter_id:   dokterDipilih.value.id,
      tanggal:     tanggalDipilih.value,
      jam:         jamDipilih.value,
      nama_pasien: form.value.nama,
      no_hp:       form.value.noHp,
      keluhan:     form.value.keluhan,
      alergi:      form.value.alergi || null,
      tipe_pasien: form.value.tipePasien,
      notif_wa:    form.value.notifWa,
    })
    hasilBooking.value = res.data.data
    step.value = 4
    window.scrollTo(0, 0)
  } catch (err) {
    const msg = err.response?.data?.message || 'Booking gagal, coba lagi'
    const errors = err.response?.data?.errors
    if (errors) {
      errorBooking.value = Object.values(errors).flat().join(', ')
    } else {
      errorBooking.value = msg
    }
  } finally {
    loadingBooking.value = false
  }
}
</script>
