<template>
  <div class="min-h-screen bg-gray-50 pb-24">
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
        <span class="text-lg font-semibold text-gray-800">Profil Saya</span>
        <button
          v-if="!showUbahPassword"
          @click="toggleEdit"
          class="text-sm text-emerald-600 font-medium hover:text-emerald-700 transition-colors"
        >
          {{ editMode ? 'Simpan' : 'Edit Profil' }}
        </button>
        <button
          v-else
          @click="showUbahPassword = false"
          class="text-sm text-gray-500 font-medium hover:text-gray-700 transition-colors"
        >
          ← Kembali
        </button>
      </div>
    </nav>

    <div class="max-w-3xl mx-auto px-6 py-8">

      <!-- ===== FORM UBAH PASSWORD ===== -->
      <div v-if="showUbahPassword">
        <h2 class="text-base font-bold text-gray-900 mb-6">Ubah Password</h2>

        <!-- Pesan sukses/error password -->
        <div v-if="pesanPassword" class="mb-4 px-4 py-3 rounded-xl text-xs font-medium"
          :class="pesanPasswordTipe === 'sukses' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100'">
          {{ pesanPassword }}
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 space-y-4">
          <div>
            <label class="text-xs text-gray-400 mb-1 block">Password Lama</label>
            <input
              v-model="passwordForm.lama"
              type="password"
              placeholder="Masukkan password lama"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:border-emerald-400 transition-colors"
            />
          </div>
          <div>
            <label class="text-xs text-gray-400 mb-1 block">Password Baru</label>
            <input
              v-model="passwordForm.baru"
              type="password"
              placeholder="Minimal 8 karakter"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:border-emerald-400 transition-colors"
            />
          </div>
          <div>
            <label class="text-xs text-gray-400 mb-1 block">Konfirmasi Password Baru</label>
            <input
              v-model="passwordForm.konfirmasi"
              type="password"
              placeholder="Ulangi password baru"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:border-emerald-400 transition-colors"
            />
          </div>
          <button
            @click="simpanPassword"
            :disabled="loadingPassword"
            class="w-full py-3 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <span v-if="loadingPassword" class="w-4 h-4 border-2 border-white/50 border-t-transparent rounded-full animate-spin"></span>
            <span v-else>Simpan Password</span>
          </button>
        </div>
      </div>

      <!-- ===== PROFIL UTAMA ===== -->
      <template v-else>
        <!-- Avatar & Name -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 mb-5 text-center">
          <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">👤</div>
          <h2 class="text-lg font-bold text-gray-900">{{ auth.namaUser }}</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ auth.user?.email }}</p>
          <div class="flex items-center justify-center gap-3 mt-3">
            <span class="text-xs bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full">Pasien Aktif</span>
          </div>
        </div>

        <!-- Pesan sukses/error profil -->
        <div v-if="pesan" class="mb-4 px-4 py-3 rounded-xl text-xs font-medium"
          :class="pesanTipe === 'sukses' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100'">
          {{ pesan }}
        </div>

        <!-- Data Diri -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 mb-5">
          <h3 class="text-sm font-semibold text-gray-800 mb-4">Data Diri</h3>
          <div class="space-y-4">
            <div v-for="field in fields" :key="field.key">
              <label class="text-xs text-gray-400 mb-1 block">{{ field.label }}</label>
              <input
                v-if="editMode && field.key !== 'email'"
                v-model="field.value"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:border-emerald-400 transition-colors"
              />
              <p v-else class="text-sm text-gray-700 bg-gray-50 rounded-xl px-4 py-2.5">
                {{ field.value || '-' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Data Kesehatan -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 mb-5">
          <h3 class="text-sm font-semibold text-gray-800 mb-4">Data Kesehatan</h3>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div v-for="h in kesehatanGrid" :key="h.key">
              <label class="text-xs text-gray-400 mb-1 block">{{ h.label }}</label>
              <input
                v-if="editMode"
                v-model="h.value"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:border-emerald-400 transition-colors"
              />
              <p v-else class="text-sm text-gray-700 bg-gray-50 rounded-xl px-4 py-2.5">
                {{ h.value || '-' }}
              </p>
            </div>
          </div>
          <!-- Riwayat Penyakit — full width -->
          <div>
            <label class="text-xs text-gray-400 mb-1 block">Riwayat Penyakit</label>
            <textarea
              v-if="editMode"
              v-model="riwayatPenyakit"
              rows="3"
              placeholder="Contoh: Hipertensi, Diabetes..."
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:border-emerald-400 transition-colors resize-none"
            />
            <p v-else class="text-sm text-gray-700 bg-gray-50 rounded-xl px-4 py-2.5 min-h-[60px]">
              {{ riwayatPenyakit || '-' }}
            </p>
          </div>
        </div>

        <!-- Menu -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-5">
          <component
            :is="menu.action ? 'button' : 'RouterLink'"
            :to="menu.to"
            v-for="menu in menus"
            :key="menu.label"
            @click="menu.action && menu.action()"
            class="w-full flex items-center justify-between px-5 py-4 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0"
          >
            <div class="flex items-center gap-3">
              <span class="text-lg">{{ menu.icon }}</span>
              {{ menu.label }}
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </component>
        </div>

        <!-- Logout -->
        <button
          @click="logout"
          class="w-full py-3.5 text-sm font-medium bg-red-50 text-red-500 border border-red-100 rounded-2xl hover:bg-red-100 transition-colors"
        >
          🚪 Keluar dari Akun
        </button>
      </template>
    </div>

    <BottomNav active="profil" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'
import BottomNav from '../components/BottomNav.vue'

const router = useRouter()
const auth = useAuthStore()

// ─── State profil ─────────────────────────────────────────────────────────────
const editMode = ref(false)
const pesan = ref('')
const pesanTipe = ref('sukses')

const fields = reactive([
  { key: 'nama',          label: 'Nama Lengkap',  value: '' },
  { key: 'nik',           label: 'NIK',           value: '' },
  { key: 'no_hp',         label: 'Nomor HP',      value: '' },
  { key: 'email',         label: 'Email',         value: '' },
  { key: 'alamat',        label: 'Alamat',        value: '' },
  { key: 'tanggal_lahir', label: 'Tanggal Lahir', value: '' },
])

// Field kesehatan grid 2 kolom
const kesehatanGrid = reactive([
  { key: 'golongan_darah', label: 'Golongan Darah', value: '' },
  { key: 'berat_badan',    label: 'Berat Badan',    value: '' },
  { key: 'tinggi_badan',   label: 'Tinggi Badan',   value: '' },
  { key: 'alergi',         label: 'Alergi',         value: '' },
])

// Riwayat penyakit terpisah karena full-width textarea
const riwayatPenyakit = ref('')

// ─── State ubah password ──────────────────────────────────────────────────────
const showUbahPassword = ref(false)
const loadingPassword = ref(false)
const pesanPassword = ref('')
const pesanPasswordTipe = ref('sukses')
const passwordForm = reactive({ lama: '', baru: '', konfirmasi: '' })

// ─── Menu ─────────────────────────────────────────────────────────────────────
const menus = [
  { icon: '🔔', label: 'Notifikasi',      to: '/notifikasi' },
  { icon: '📋', label: 'Riwayat Antrian', to: '/antrian' },
  { icon: '📄', label: 'Rekam Medis',     to: '/rekam-medis' },
  {
    icon: '🔒',
    label: 'Ubah Password',
    action: () => {
      showUbahPassword.value = true
      pesanPassword.value = ''
      passwordForm.lama = ''
      passwordForm.baru = ''
      passwordForm.konfirmasi = ''
    },
  },
]

// ─── Helpers ──────────────────────────────────────────────────────────────────
function isiForm() {
  const u = auth.user
  if (!u) return

  fields.find((f) => f.key === 'nama').value          = u.nama || ''
  fields.find((f) => f.key === 'nik').value           = u.nik || ''
  fields.find((f) => f.key === 'no_hp').value         = u.no_hp || ''
  fields.find((f) => f.key === 'email').value         = u.email || ''
  fields.find((f) => f.key === 'alamat').value        = u.alamat || ''
  fields.find((f) => f.key === 'tanggal_lahir').value = u.tanggal_lahir || ''

  const pk = u.profil_kesehatan
  if (pk) {
    kesehatanGrid.find((k) => k.key === 'golongan_darah').value = pk.golongan_darah || ''
    kesehatanGrid.find((k) => k.key === 'berat_badan').value    = pk.berat_badan ? pk.berat_badan + ' kg' : ''
    kesehatanGrid.find((k) => k.key === 'tinggi_badan').value   = pk.tinggi_badan ? pk.tinggi_badan + ' cm' : ''
    kesehatanGrid.find((k) => k.key === 'alergi').value         = pk.alergi || ''
    riwayatPenyakit.value                                       = pk.riwayat_penyakit || ''
  }
}

async function toggleEdit() {
  if (!editMode.value) {
    editMode.value = true
    return
  }

  // Simpan profil
  try {
    const profilPayload = {}
    fields.forEach((f) => {
      if (f.key !== 'email') profilPayload[f.key] = f.value
    })
    await api.put('/profil', profilPayload)

    const kesehatanPayload = {}
    kesehatanGrid.forEach((k) => {
      kesehatanPayload[k.key] = k.value.replace(' kg', '').replace(' cm', '')
    })
    kesehatanPayload.riwayat_penyakit = riwayatPenyakit.value

    await api.put('/profil/kesehatan', kesehatanPayload)

    await auth.fetchMe()
    isiForm()

    pesan.value = 'Profil berhasil disimpan'
    pesanTipe.value = 'sukses'
    setTimeout(() => (pesan.value = ''), 3000)
  } catch (err) {
    pesan.value = err.response?.data?.message || 'Gagal menyimpan profil'
    pesanTipe.value = 'error'
  }

  editMode.value = false
}

async function simpanPassword() {
  pesanPassword.value = ''

  if (!passwordForm.lama || !passwordForm.baru || !passwordForm.konfirmasi) {
    pesanPassword.value = 'Semua field wajib diisi'
    pesanPasswordTipe.value = 'error'
    return
  }

  if (passwordForm.baru !== passwordForm.konfirmasi) {
    pesanPassword.value = 'Konfirmasi password tidak cocok'
    pesanPasswordTipe.value = 'error'
    return
  }

  if (passwordForm.baru.length < 8) {
    pesanPassword.value = 'Password baru minimal 8 karakter'
    pesanPasswordTipe.value = 'error'
    return
  }

  loadingPassword.value = true
  try {
    await api.put('/profil/password', {
      password_lama:          passwordForm.lama,
      password:               passwordForm.baru,
      password_confirmation:  passwordForm.konfirmasi,
    })
    pesanPassword.value = 'Password berhasil diubah'
    pesanPasswordTipe.value = 'sukses'
    passwordForm.lama = ''
    passwordForm.baru = ''
    passwordForm.konfirmasi = ''
  } catch (err) {
    pesanPassword.value = err.response?.data?.message || 'Gagal mengubah password'
    pesanPasswordTipe.value = 'error'
  } finally {
    loadingPassword.value = false
  }
}

async function logout() {
  await auth.logout()
  router.push('/login')
}

onMounted(async () => {
  await auth.fetchMe()
  isiForm()
})
</script>
