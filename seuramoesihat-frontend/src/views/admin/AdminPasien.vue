<template>
  <div class="p-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Kelola Pasien</h1>
      <p class="text-sm text-gray-500 mt-1">Daftar semua akun pasien yang terdaftar</p>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-5">
      <input
        v-model="search"
        type="text"
        placeholder="Cari nama atau email pasien..."
        class="border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-emerald-400 w-full max-w-sm"
        @input="debouncedLoad"
      />
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400 text-sm">Memuat data...</div>
      <div v-else-if="users.length === 0" class="p-12 text-center">
        <div class="text-4xl mb-3">👥</div>
        <div class="text-gray-500 text-sm">Tidak ada pasien ditemukan</div>
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Nama</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Email</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">No. HP</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Total Antrian</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Terdaftar</th>
            <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-4 font-medium text-gray-800">{{ u.nama }}</td>
            <td class="px-5 py-4 text-gray-600">{{ u.email }}</td>
            <td class="px-5 py-4 text-gray-600">{{ u.no_hp || '-' }}</td>
            <td class="px-5 py-4 text-gray-600">{{ u.total_antrian }} antrian</td>
            <td class="px-5 py-4 text-gray-500 text-xs">{{ u.created_at }}</td>
            <td class="px-5 py-4">
              <button
                @click="confirmDelete(u.id, u.nama)"
                class="text-xs text-red-500 hover:text-red-700 px-2 py-1.5 rounded-lg hover:bg-red-50 transition-colors"
              >Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <div class="text-xs text-gray-500">Total {{ meta.total }} pasien</div>
        <div class="flex gap-2">
          <button
            v-for="p in meta.last_page" :key="p"
            @click="page = p; loadUsers()"
            class="w-8 h-8 rounded-lg text-xs transition-colors"
            :class="page === p ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
          >{{ p }}</button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-80 shadow-xl">
        <div class="text-lg font-semibold text-gray-900 mb-2">Hapus Pasien?</div>
        <p class="text-sm text-gray-500 mb-1">Akun <strong>{{ deleteTarget.nama }}</strong> akan dihapus permanen.</p>
        <p class="text-xs text-red-500 mb-5">Semua data antrian dan rekam medis pasien ini juga akan terhapus.</p>
        <div class="flex gap-3">
          <button @click="deleteTarget = null" class="flex-1 border border-gray-200 text-gray-600 text-sm py-2.5 rounded-xl hover:bg-gray-50 transition-colors">Batal</button>
          <button @click="doDelete" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm py-2.5 rounded-xl transition-colors">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api/axios'

const users       = ref([])
const loading     = ref(false)
const search      = ref('')
const page        = ref(1)
const meta        = ref({ last_page: 1, total: 0 })
const deleteTarget = ref(null)

let debounceTimer = null
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; loadUsers() }, 400)
}

async function loadUsers() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (search.value) params.search = search.value
    const res = await api.get('/admin/users', { params })
    users.value = res.data.data
    meta.value  = res.data.meta
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

function confirmDelete(id, nama) {
  deleteTarget.value = { id, nama }
}

async function doDelete() {
  try {
    await api.delete(`/admin/users/${deleteTarget.value.id}`)
    deleteTarget.value = null
    await loadUsers()
  } catch {
    deleteTarget.value = null
  }
}

onMounted(loadUsers)
</script>
