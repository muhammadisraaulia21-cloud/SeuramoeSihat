<template>
  <div class="p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Dokter</h1>
        <p class="text-sm text-gray-500 mt-1">Tambah, edit, dan kelola ketersediaan dokter</p>
      </div>
      <button
        @click="openForm()"
        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-colors"
      >
        <span class="text-base leading-none">+</span> Tambah Dokter
      </button>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-5">
      <input
        v-model="search"
        type="text"
        placeholder="Cari nama dokter..."
        class="border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-emerald-400 w-full max-w-sm"
        @input="debouncedLoad"
      />
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Memuat data...</div>
      <div v-else-if="dokters.length === 0" class="p-12 text-center">
        <div class="text-4xl mb-3">👨‍⚕️</div>
        <div class="text-gray-500 text-sm mb-4">Belum ada dokter terdaftar</div>
        <button @click="openForm()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-xl transition-colors">
          + Tambah Dokter Pertama
        </button>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Dokter</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Faskes</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Jadwal</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Rating</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Status</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="d in dokters" :key="d.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-4">
              <!-- Avatar + nama -->
              <div class="flex items-center gap-3">
                <div
                  class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
                  :style="{ background: d.avatar_bg, color: d.avatar_color }"
                >{{ d.inisial }}</div>
                <div>
                  <div class="font-medium text-gray-800">{{ d.nama }}</div>
                  <div class="text-xs text-gray-400">{{ d.spesialis }}</div>
                </div>
              </div>
            </td>
            <td class="px-5 py-4">
              <div class="text-gray-700">{{ d.faskes }}</div>
              <div class="text-xs text-gray-400">{{ d.faskes_wilayah }}</div>
            </td>
            <td class="px-5 py-4">
              <div v-if="d.jadwal.length" class="space-y-0.5">
                <div v-for="j in d.jadwal" :key="j.hari" class="text-xs text-gray-500">
                  {{ namaHari[j.hari] }}: {{ j.jam_mulai }}–{{ j.jam_selesai }}
                </div>
              </div>
              <span v-else class="text-xs text-gray-400">Belum ada jadwal</span>
            </td>
            <td class="px-5 py-4 text-gray-600">⭐ {{ d.rating }}</td>
            <td class="px-5 py-4">
              <span
                class="text-xs px-2.5 py-1 rounded-full font-medium cursor-pointer"
                :class="d.aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'"
                @click="toggleAktif(d)"
              >{{ d.aktif ? 'Aktif' : 'Nonaktif' }}</span>
            </td>
            <td class="px-5 py-4">
              <div class="flex items-center gap-2">
                <button
                  @click="openForm(d)"
                  class="text-xs text-blue-600 hover:text-blue-800 px-2 py-1.5 rounded-lg hover:bg-blue-50 transition-colors"
                >Edit</button>
                <button
                  @click="confirmDelete(d)"
                  class="text-xs text-red-500 hover:text-red-700 px-2 py-1.5 rounded-lg hover:bg-red-50 transition-colors"
                >Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <div class="text-xs text-gray-500">Total {{ meta.total }} dokter</div>
        <div class="flex gap-2">
          <button
            v-for="p in meta.last_page" :key="p"
            @click="page = p; loadDokter()"
            class="w-8 h-8 rounded-lg text-xs transition-colors"
            :class="page === p ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
          >{{ p }}</button>
        </div>
      </div>
    </div>

    <!-- ── Modal Form Tambah/Edit Dokter ── -->
    <div v-if="formModal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">

        <!-- Header modal -->
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
          <div class="text-lg font-semibold text-gray-900">
            {{ formModal.editId ? 'Edit Dokter' : 'Tambah Dokter Baru' }}
          </div>
          <button @click="formModal.open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
        </div>

        <div class="p-6 space-y-5">

          <!-- Nama & Faskes -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
              <input
                v-model="form.nama"
                type="text"
                placeholder="dr. Nama Dokter, Sp.X"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-emerald-400"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Faskes <span class="text-red-500">*</span></label>
              <select
                v-model="form.faskes_id"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-emerald-400"
              >
                <option value="">Pilih Faskes</option>
                <option v-for="f in faksesOptions" :key="f.id" :value="f.id">
                  {{ f.nama }} ({{ f.wilayah }})
                </option>
              </select>
            </div>
          </div>

          <!-- Spesialis & Kategori -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Spesialis <span class="text-red-500">*</span></label>
              <input
                v-model="form.spesialis"
                type="text"
                placeholder="Dokter Umum / Spesialis Anak"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-emerald-400"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
              <select
                v-model="form.kategori"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-emerald-400"
              >
                <option value="">Pilih Kategori</option>
                <option v-for="k in kategoriOptions" :key="k" :value="k">{{ k }}</option>
              </select>
            </div>
          </div>

          <!-- Pengalaman & Jumlah Pasien -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Pengalaman</label>
              <input
                v-model="form.pengalaman"
                type="text"
                placeholder="8 tahun"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-emerald-400"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah Pasien</label>
              <input
                v-model="form.jumlah_pasien"
                type="text"
                placeholder="1.200+"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-emerald-400"
              />
            </div>
          </div>

          <!-- Warna Avatar -->
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-2">Warna Avatar</label>
            <div class="flex gap-3 flex-wrap">
              <div
                v-for="p in avatarPalette" :key="p.bg"
                @click="form.avatar_bg = p.bg; form.avatar_color = p.color"
                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold cursor-pointer border-2 transition-all"
                :style="{ background: p.bg, color: p.color }"
                :class="form.avatar_bg === p.bg ? 'border-gray-800 scale-110' : 'border-transparent'"
              >{{ avatarInisial }}</div>
            </div>
          </div>

          <!-- Tentang -->
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Tentang Dokter</label>
            <textarea
              v-model="form.tentang"
              rows="3"
              placeholder="Deskripsi singkat tentang dokter..."
              class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-emerald-400 resize-none"
            />
          </div>

          <!-- Keahlian -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-medium text-gray-700">Keahlian</label>
              <button @click="tambahKeahlian" class="text-xs text-emerald-600 hover:text-emerald-700">+ Tambah</button>
            </div>
            <div class="flex flex-wrap gap-2">
              <div
                v-for="(k, i) in form.keahlian" :key="i"
                class="flex items-center gap-1 bg-emerald-50 border border-emerald-100 rounded-full px-3 py-1"
              >
                <input
                  v-model="form.keahlian[i]"
                  type="text"
                  class="text-xs text-emerald-700 bg-transparent outline-none w-28"
                  placeholder="Keahlian..."
                />
                <button @click="form.keahlian.splice(i, 1)" class="text-emerald-400 hover:text-red-500 ml-1 text-xs">✕</button>
              </div>
            </div>
          </div>

          <!-- Jadwal Praktik -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <label class="text-xs font-medium text-gray-700">Jadwal Praktik</label>
              <button @click="tambahJadwal" class="text-xs text-emerald-600 hover:text-emerald-700">+ Tambah Hari</button>
            </div>
            <div class="space-y-2">
              <div
                v-for="(j, i) in form.jadwal" :key="i"
                class="flex items-center gap-3 bg-gray-50 rounded-xl p-3"
              >
                <select
                  v-model="j.hari"
                  class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:border-emerald-400 bg-white"
                >
                  <option v-for="(h, idx) in namaHari" :key="idx" :value="idx">{{ h }}</option>
                </select>
                <input v-model="j.jam_mulai"   type="time" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:border-emerald-400 bg-white" />
                <span class="text-gray-400 text-xs">s/d</span>
                <input v-model="j.jam_selesai" type="time" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:border-emerald-400 bg-white" />
                <div class="flex items-center gap-1">
                  <input v-model.number="j.kuota_per_hari" type="number" min="1" max="100" class="w-14 border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:border-emerald-400 bg-white" />
                  <span class="text-xs text-gray-400">kuota</span>
                </div>
                <button @click="form.jadwal.splice(i, 1)" class="text-red-400 hover:text-red-600 text-xs ml-auto">✕</button>
              </div>
              <div v-if="form.jadwal.length === 0" class="text-xs text-gray-400 text-center py-3 bg-gray-50 rounded-xl">
                Belum ada jadwal. Klik "+ Tambah Hari" untuk menambahkan.
              </div>
            </div>
          </div>
        </div>

        <!-- Footer modal -->
        <div class="px-6 py-4 border-t border-gray-100 flex gap-3 sticky bottom-0 bg-white rounded-b-2xl">
          <button
            @click="formModal.open = false"
            class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors"
          >Batal</button>
          <button
            @click="simpanDokter"
            :disabled="formModal.saving || !form.nama || !form.faskes_id || !form.spesialis || !form.kategori"
            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm py-2.5 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >{{ formModal.saving ? 'Menyimpan...' : (formModal.editId ? 'Simpan Perubahan' : 'Tambah Dokter') }}</button>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-80 shadow-xl">
        <div class="text-lg font-semibold text-gray-900 mb-2">Hapus Dokter?</div>
        <p class="text-sm text-gray-500 mb-1">Dokter <strong>{{ deleteTarget.nama }}</strong> akan dihapus permanen.</p>
        <p class="text-xs text-red-500 mb-5">Semua jadwal dan data terkait dokter ini juga akan terhapus.</p>
        <div class="flex gap-3">
          <button @click="deleteTarget = null" class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
          <button @click="doDelete" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm py-2.5 rounded-xl transition-colors">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../api/axios'

// ─── Data ─────────────────────────────────────────────────────────────────────
const dokters      = ref([])
const faksesOptions = ref([])
const loading      = ref(false)
const search       = ref('')
const page         = ref(1)
const meta         = ref({ last_page: 1, total: 0 })
const deleteTarget = ref(null)

const namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
const kategoriOptions = ['Dokter Umum', 'Spesialis Anak', 'Gigi', 'Kandungan', 'Penyakit Dalam']

const avatarPalette = [
  { bg: '#E1F5EE', color: '#0F6E56' },
  { bg: '#E6F1FB', color: '#185FA5' },
  { bg: '#FAEEDA', color: '#854F0B' },
  { bg: '#FBEAF0', color: '#993556' },
  { bg: '#EEEDFE', color: '#534AB7' },
  { bg: '#FEF3C7', color: '#92400E' },
  { bg: '#FCE7F3', color: '#9D174D' },
  { bg: '#ECFDF5', color: '#065F46' },
]

// ─── Form ─────────────────────────────────────────────────────────────────────
const formModal = ref({ open: false, saving: false, editId: null })

const emptyForm = () => ({
  nama: '', faskes_id: '', spesialis: '', kategori: '',
  pengalaman: '', jumlah_pasien: '',
  tentang: '', keahlian: [],
  avatar_bg: '#E1F5EE', avatar_color: '#0F6E56',
  jadwal: [],
})

const form = ref(emptyForm())

const avatarInisial = computed(() => {
  const kata = form.value.nama.trim().split(/\s+/).filter(k => /[a-zA-Z]/.test(k))
  return kata.slice(0, 2).map(k => k.replace(/[^a-zA-Z]/g, '')[0] || '').join('').toUpperCase() || 'DR'
})

function openForm(dokter = null) {
  if (dokter) {
    form.value = {
      nama:           dokter.nama,
      faskes_id:      dokter.faskes_id,
      spesialis:      dokter.spesialis,
      kategori:       dokter.kategori,
      pengalaman:     dokter.pengalaman || '',
      jumlah_pasien:  dokter.jumlah_pasien || '',
      tentang:        dokter.tentang || '',
      keahlian:       [...(dokter.keahlian || [])],
      avatar_bg:      dokter.avatar_bg,
      avatar_color:   dokter.avatar_color,
      jadwal:         dokter.jadwal.map(j => ({ ...j })),
    }
    formModal.value.editId = dokter.id
  } else {
    form.value = emptyForm()
    formModal.value.editId = null
  }
  formModal.value.open = true
  formModal.value.saving = false
}

function tambahKeahlian() {
  form.value.keahlian.push('')
}

function tambahJadwal() {
  form.value.jadwal.push({ hari: 1, jam_mulai: '08:00', jam_selesai: '12:00', kuota_per_hari: 20 })
}

async function simpanDokter() {
  formModal.value.saving = true
  try {
    const payload = {
      ...form.value,
      keahlian: form.value.keahlian.filter(k => k.trim()),
    }
    if (formModal.value.editId) {
      await api.put(`/admin/dokter/${formModal.value.editId}`, payload)
    } else {
      await api.post('/admin/dokter', payload)
    }
    formModal.value.open = false
    await loadDokter()
  } catch (err) {
    // silent — bisa tambah toast di sini
  } finally {
    formModal.value.saving = false
  }
}

// ─── Toggle aktif ─────────────────────────────────────────────────────────────
async function toggleAktif(d) {
  try {
    await api.patch(`/admin/dokter/${d.id}/tersedia`, { tersedia: !d.aktif })
    d.aktif = !d.aktif
  } catch {
    // silent
  }
}

// ─── Hapus ────────────────────────────────────────────────────────────────────
function confirmDelete(d) {
  deleteTarget.value = d
}

async function doDelete() {
  try {
    await api.delete(`/admin/dokter/${deleteTarget.value.id}`)
    deleteTarget.value = null
    await loadDokter()
  } catch {
    deleteTarget.value = null
  }
}

// ─── Load data ────────────────────────────────────────────────────────────────
let debounceTimer = null
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; loadDokter() }, 400)
}

async function loadDokter() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (search.value) params.search = search.value
    const res = await api.get('/admin/dokter', { params })
    dokters.value = res.data.data
    meta.value    = res.data.meta
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

async function loadFaskes() {
  try {
    const res = await api.get('/admin/faskes')
    faksesOptions.value = res.data.data
  } catch {
    // silent
  }
}

onMounted(() => {
  loadDokter()
  loadFaskes()
})
</script>
