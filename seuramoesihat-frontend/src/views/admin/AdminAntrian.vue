<template>
  <div class="p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Antrian</h1>
        <p class="text-sm text-gray-500 mt-1">Approve, panggil, dan selesaikan antrian pasien</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3 items-center">
      <input
        v-model="filters.tanggal"
        type="date"
        class="border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-emerald-400"
        @change="loadAntrian"
      />
      <select
        v-model="filters.status"
        class="border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-emerald-400"
        @change="loadAntrian"
      >
        <option value="">Semua Status</option>
        <option value="menunggu">Menunggu</option>
        <option value="dipanggil">Dipanggil</option>
        <option value="selesai">Selesai</option>
        <option value="dibatalkan">Dibatalkan</option>
      </select>
      <input
        v-model="filters.search"
        type="text"
        placeholder="Cari nama pasien..."
        class="border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-emerald-400 flex-1 min-w-40"
        @input="debouncedLoad"
      />
      <button
        @click="resetFilter"
        class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-50 transition-colors"
      >Reset</button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <!-- Loading -->
      <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Memuat data...</div>

      <!-- Empty -->
      <div v-else-if="antrian.length === 0" class="p-12 text-center">
        <div class="text-4xl mb-3">📋</div>
        <div class="text-gray-500 text-sm">Tidak ada antrian ditemukan</div>
      </div>

      <!-- Data -->
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">No</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Pasien</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Dokter / Faskes</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Tanggal & Jam</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Keluhan</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Status</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="a in antrian" :key="a.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-4 font-semibold text-gray-700">#{{ a.nomor_antrian }}</td>
            <td class="px-5 py-4">
              <div class="font-medium text-gray-800">{{ a.nama_pasien }}</div>
              <div class="text-xs text-gray-400">{{ a.no_hp }}</div>
              <div class="text-xs text-gray-400">{{ a.tipe_pasien }}</div>
            </td>
            <td class="px-5 py-4">
              <div class="font-medium text-gray-800">{{ a.dokter?.nama }}</div>
              <div class="text-xs text-gray-400">{{ a.dokter?.spesialis }}</div>
              <div class="text-xs text-gray-400">{{ a.faskes }}</div>
            </td>
            <td class="px-5 py-4">
              <div class="text-gray-700">{{ a.tanggal }}</div>
              <div class="text-xs text-gray-400">{{ a.jam }}</div>
            </td>
            <td class="px-5 py-4 max-w-xs">
              <div class="text-gray-600 text-xs line-clamp-2">{{ a.keluhan }}</div>
            </td>
            <td class="px-5 py-4">
              <span
                class="text-xs px-2.5 py-1 rounded-full font-medium"
                :class="statusClass(a.status)"
              >{{ statusLabel(a.status) }}</span>
            </td>
            <td class="px-5 py-4">
              <div class="flex items-center gap-2 flex-wrap">
                <!-- Tombol aksi berdasarkan status -->
                <button
                  v-if="a.status === 'menunggu'"
                  @click="updateStatus(a.id, 'dipanggil')"
                  :disabled="updating === a.id"
                  class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50"
                >Panggil</button>

                <button
                  v-if="a.status === 'dipanggil'"
                  @click="updateStatus(a.id, 'selesai')"
                  :disabled="updating === a.id"
                  class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50"
                >Selesai</button>

                <button
                  v-if="['menunggu', 'dipanggil'].includes(a.status)"
                  @click="updateStatus(a.id, 'dibatalkan')"
                  :disabled="updating === a.id"
                  class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50"
                >Batalkan</button>

                <button
                  v-if="['selesai', 'dibatalkan'].includes(a.status)"
                  @click="confirmDelete(a.id)"
                  class="text-xs text-red-500 hover:text-red-700 px-2 py-1.5 rounded-lg hover:bg-red-50 transition-colors"
                >Hapus</button>

                <button
                  v-if="a.status === 'selesai'"
                  @click="openRekamMedis(a)"
                  class="text-xs text-purple-600 hover:text-purple-800 px-2 py-1.5 rounded-lg hover:bg-purple-50 transition-colors"
                >+ Rekam Medis</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <div class="text-xs text-gray-500">Total {{ meta.total }} antrian</div>
        <div class="flex gap-2">
          <button
            v-for="p in meta.last_page"
            :key="p"
            @click="page = p; loadAntrian()"
            class="w-8 h-8 rounded-lg text-xs transition-colors"
            :class="page === p ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
          >{{ p }}</button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="deleteId" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-80 shadow-xl">
        <div class="text-lg font-semibold text-gray-900 mb-2">Hapus Antrian?</div>
        <p class="text-sm text-gray-500 mb-5">Data antrian ini akan dihapus permanen dan tidak bisa dikembalikan.</p>
        <div class="flex gap-3">
          <button @click="deleteId = null" class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
          <button @click="doDelete" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm py-2.5 rounded-xl transition-colors">Hapus</button>
        </div>
      </div>
    </div>

    <!-- Modal Rekam Medis -->
    <div v-if="rmModal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100">
          <div class="text-lg font-semibold text-gray-900">Buat Rekam Medis</div>
          <div class="text-xs text-gray-500 mt-1">
            {{ rmModal.antrian?.nama_pasien }} — {{ rmModal.antrian?.dokter?.nama }}
          </div>
        </div>
        <div class="p-6 space-y-4">
          <!-- Diagnosa -->
          <div>
            <label class="text-xs font-medium text-gray-700 mb-1 block">Diagnosa <span class="text-red-500">*</span></label>
            <textarea
              v-model="rmModal.diagnosa"
              rows="2"
              placeholder="Contoh: ISPA ringan"
              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-emerald-400 resize-none"
            />
          </div>
          <!-- Catatan Dokter -->
          <div>
            <label class="text-xs font-medium text-gray-700 mb-1 block">Catatan Dokter</label>
            <textarea
              v-model="rmModal.catatan"
              rows="2"
              placeholder="Anjuran dan catatan untuk pasien"
              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-emerald-400 resize-none"
            />
          </div>
          <!-- Resep Obat -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-medium text-gray-700">Resep Obat</label>
              <button @click="tambahResep" class="text-xs text-emerald-600 hover:text-emerald-700">+ Tambah Obat</button>
            </div>
            <div v-for="(r, i) in rmModal.resep" :key="i" class="flex gap-2 mb-2">
              <input v-model="r.nama_obat"    placeholder="Nama obat"    class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-emerald-400" />
              <input v-model="r.dosis"        placeholder="Dosis"        class="w-20 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-emerald-400" />
              <input v-model="r.aturan_pakai" placeholder="Aturan"       class="w-20 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-emerald-400" />
              <button @click="rmModal.resep.splice(i, 1)" class="text-red-400 hover:text-red-600 px-1">✕</button>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex gap-3">
          <button @click="rmModal.open = false" class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
          <button
            @click="simpanRekamMedis"
            :disabled="rmModal.saving || !rmModal.diagnosa"
            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm py-2.5 rounded-xl transition-colors disabled:opacity-50"
          >{{ rmModal.saving ? 'Menyimpan...' : 'Simpan Rekam Medis' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api/axios'

const antrian = ref([])
const loading = ref(false)
const updating = ref(null)
const deleteId = ref(null)
const page = ref(1)
const meta = ref({ last_page: 1, total: 0 })

const filters = ref({
  tanggal: new Date().toISOString().split('T')[0], // default hari ini
  status: '',
  search: '',
})

// ─── Modal Rekam Medis ────────────────────────────────────────────────────────
const rmModal = ref({
  open: false, saving: false,
  antrian: null, diagnosa: '', catatan: '',
  resep: [],
})

function openRekamMedis(a) {
  rmModal.value = {
    open: true, saving: false,
    antrian: a, diagnosa: '', catatan: '',
    resep: [{ nama_obat: '', dosis: '', aturan_pakai: '' }],
  }
}

function tambahResep() {
  rmModal.value.resep.push({ nama_obat: '', dosis: '', aturan_pakai: '' })
}

async function simpanRekamMedis() {
  if (!rmModal.value.diagnosa) return
  rmModal.value.saving = true
  try {
    const resepBersih = rmModal.value.resep.filter(r => r.nama_obat.trim())
    await api.post(`/admin/antrian/${rmModal.value.antrian.id}/rekam-medis`, {
      diagnosa:       rmModal.value.diagnosa,
      catatan_dokter: rmModal.value.catatan,
      resep:          resepBersih,
    })
    rmModal.value.open = false
  } catch {
    // silent
  } finally {
    rmModal.value.saving = false
  }
}

let debounceTimer = null
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadAntrian(), 400)
}

async function loadAntrian() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (filters.value.tanggal) params.tanggal = filters.value.tanggal
    if (filters.value.status)  params.status  = filters.value.status
    if (filters.value.search)  params.search  = filters.value.search

    const res = await api.get('/admin/antrian', { params })
    antrian.value = res.data.data
    meta.value    = res.data.meta
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

async function updateStatus(id, status) {
  updating.value = id
  try {
    await api.patch(`/admin/antrian/${id}/status`, { status })
    await loadAntrian()
  } catch {
    // silent
  } finally {
    updating.value = null
  }
}

function confirmDelete(id) {
  deleteId.value = id
}

async function doDelete() {
  try {
    await api.delete(`/admin/antrian/${deleteId.value}`)
    deleteId.value = null
    await loadAntrian()
  } catch {
    deleteId.value = null
  }
}

function resetFilter() {
  filters.value = { tanggal: '', status: '', search: '' }
  page.value = 1
  loadAntrian()
}

function statusLabel(s) {
  return { menunggu: 'Menunggu', dipanggil: 'Dipanggil', selesai: 'Selesai', dibatalkan: 'Dibatalkan' }[s] ?? s
}

function statusClass(s) {
  return {
    menunggu:   'bg-yellow-50 text-yellow-700',
    dipanggil:  'bg-blue-50 text-blue-700',
    selesai:    'bg-emerald-50 text-emerald-700',
    dibatalkan: 'bg-red-50 text-red-600',
  }[s] ?? 'bg-gray-100 text-gray-600'
}

onMounted(loadAntrian)
</script>
