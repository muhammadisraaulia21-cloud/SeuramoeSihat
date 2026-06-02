<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- NAVBAR -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-3xl mx-auto px-6 py-4 flex items-center gap-4">
        <button
          @click="activeChat ? tutupChat() : $router.back()"
          class="w-9 h-9 border border-gray-200 rounded-xl flex items-center justify-center hover:bg-gray-50 transition-colors"
        >
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <div v-if="activeChat" class="flex items-center gap-3 flex-1">
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
            :style="{ background: activeChat.avatar_bg, color: activeChat.avatar_color }"
          >
            {{ activeChat.inisial }}
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800 leading-none">{{ activeChat.nama }}</p>
            <div class="flex items-center gap-1 mt-0.5">
              <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
              <span class="text-xs text-emerald-600">Online</span>
            </div>
          </div>
        </div>
        <span v-else class="text-sm font-semibold text-gray-800">Konsultasi Chat</span>
      </div>
    </nav>

    <!-- LIST DOKTER (jika belum pilih chat) -->
    <div v-if="!activeChat" class="max-w-3xl mx-auto px-6 py-8 w-full flex-1">
      <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 mb-1">Konsultasi Chat</h1>
        <p class="text-sm text-gray-500">Tanya keluhan ringan langsung ke dokter</p>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="text-center py-12">
        <div class="w-8 h-8 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
        <p class="text-sm text-gray-400">Memuat data...</p>
      </div>

      <template v-else>
        <!-- Search -->
        <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-3 mb-6">
          <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchDokter"
            type="text"
            placeholder="Cari dokter..."
            class="flex-1 text-sm text-gray-700 placeholder:text-gray-400 outline-none bg-transparent"
          />
        </div>

        <!-- Riwayat chat -->
        <div v-if="store.riwayatChat.length > 0" class="mb-6">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Riwayat Chat</p>
          <div class="space-y-2">
            <div
              v-for="chat in store.riwayatChat"
              :key="chat.id"
              @click="bukaChat(chat.dokter, chat.id)"
              class="bg-white rounded-2xl p-4 border border-gray-100 hover:border-emerald-200 hover:shadow-sm transition-all duration-200 cursor-pointer flex items-center gap-4"
            >
              <div class="relative">
                <div
                  class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0"
                  :style="{ background: chat.dokter.avatar_bg, color: chat.dokter.avatar_color }"
                >
                  {{ chat.dokter.inisial }}
                </div>
                <span
                  v-if="chat.dokter.online"
                  class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"
                ></span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-0.5">
                  <p class="text-sm font-semibold text-gray-800 truncate">{{ chat.dokter.nama }}</p>
                  <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ chat.waktu }}</span>
                </div>
                <p class="text-xs text-gray-500 truncate">{{ chat.pesan_terakhir }}</p>
              </div>
              <div
                v-if="chat.unread > 0"
                class="w-5 h-5 bg-emerald-600 rounded-full flex items-center justify-center flex-shrink-0"
              >
                <span class="text-white text-xs font-bold">{{ chat.unread }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Semua dokter -->
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Dokter Tersedia</p>
        <div class="space-y-2">
          <div
            v-for="dokter in filteredDokterList"
            :key="dokter.id"
            @click="bukaChat(dokter)"
            class="bg-white rounded-2xl p-4 border border-gray-100 hover:border-emerald-200 hover:shadow-sm transition-all duration-200 cursor-pointer flex items-center gap-4"
          >
            <div class="relative">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0"
                :style="{ background: dokter.avatar_bg, color: dokter.avatar_color }"
              >
                {{ dokter.inisial }}
              </div>
              <span
                v-if="dokter.online"
                class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"
              ></span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-800">{{ dokter.nama }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ dokter.spesialis }}</p>
              <div class="flex gap-2 mt-1.5">
                <span
                  class="text-xs px-2 py-0.5 rounded-full font-medium"
                  :class="dokter.online ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                >
                  {{ dokter.online ? 'Online' : 'Offline' }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-yellow-50 text-yellow-700">
                  ⭐ {{ dokter.rating }}
                </span>
              </div>
            </div>
            <div class="text-xs text-gray-400 text-right flex-shrink-0">
              <p>Resp.</p>
              <p class="font-medium text-gray-600">{{ dokter.respon_time }}</p>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- CHAT ROOM -->
    <div v-if="activeChat" class="flex flex-col flex-1 max-w-3xl mx-auto w-full">
      <!-- Info dokter -->
      <div class="bg-white border-b border-gray-100 px-6 py-3">
        <div class="flex items-center gap-3">
          <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-blue-50 text-blue-700">
            {{ activeChat.spesialis }}
          </span>
          <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-600">
            {{ activeChat.faskes }}
          </span>
          <span class="text-xs text-gray-400 ml-auto">Konsultasi ringan & cepat</span>
        </div>
      </div>

      <!-- Pesan -->
      <div
        class="flex-1 overflow-y-auto px-6 py-5 space-y-4"
        ref="chatContainer"
        style="max-height: calc(100vh - 220px)"
      >
        <!-- Loading pesan -->
        <div v-if="store.loadingPesan" class="text-center py-8">
          <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
        </div>

        <template v-else>
          <div class="text-center">
            <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Hari ini</span>
          </div>

          <!-- Bubble pesan -->
          <div
            v-for="(pesan, i) in store.pesanList"
            :key="i"
            class="flex"
            :class="pesan.dari === 'pasien' ? 'justify-end' : 'justify-start'"
          >
            <!-- Avatar dokter -->
            <div
              v-if="pesan.dari === 'dokter'"
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0 mr-2 mt-1"
              :style="{ background: activeChat.avatar_bg, color: activeChat.avatar_color }"
            >
              {{ activeChat.inisial }}
            </div>

            <div class="max-w-[75%]">
              <!-- Typing indicator -->
              <div
                v-if="pesan.typing"
                class="bg-white border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-3 flex gap-1 items-center"
              >
                <span
                  v-for="j in 3"
                  :key="j"
                  class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                  :style="{ animationDelay: j * 0.15 + 's' }"
                ></span>
              </div>

              <!-- Pesan normal -->
              <div
                v-else
                class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed"
                :class="
                  pesan.dari === 'pasien'
                    ? 'bg-emerald-600 text-white rounded-tr-sm'
                    : 'bg-white border border-gray-100 text-gray-800 rounded-tl-sm'
                "
              >
                {{ pesan.teks }}
              </div>

              <p
                class="text-xs text-gray-400 mt-1"
                :class="pesan.dari === 'pasien' ? 'text-right' : 'text-left'"
              >
                {{ pesan.waktu }}
                <span v-if="pesan.dari === 'pasien'" class="ml-1">
                  {{ pesan.dibaca ? '✓✓' : '✓' }}
                </span>
              </p>
            </div>
          </div>

          <!-- Typing indicator sementara -->
          <div v-if="isTyping" class="flex justify-start">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0 mr-2 mt-1"
              :style="{ background: activeChat.avatar_bg, color: activeChat.avatar_color }"
            >
              {{ activeChat.inisial }}
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-3 flex gap-1 items-center">
              <span v-for="j in 3" :key="j" class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" :style="{ animationDelay: j * 0.15 + 's' }"></span>
            </div>
          </div>
        </template>
      </div>

      <!-- Quick replies -->
      <div v-if="showQuickReply" class="px-6 pb-2 flex gap-2 overflow-x-auto scrollbar-hide">
        <button
          v-for="q in quickReplies"
          :key="q"
          @click="kirimPesan(q)"
          class="flex-shrink-0 text-xs bg-white border border-emerald-200 text-emerald-600 px-3 py-1.5 rounded-full hover:bg-emerald-50 transition-colors whitespace-nowrap"
        >
          {{ q }}
        </button>
      </div>

      <!-- Input pesan -->
      <div class="bg-white border-t border-gray-100 px-4 py-3 sticky bottom-0">
        <div class="flex items-end gap-3">
          <div
            class="flex-1 bg-gray-50 border border-gray-200 rounded-2xl px-4 py-2.5 flex items-end gap-2 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100 transition-all"
          >
            <textarea
              v-model="inputPesan"
              @keydown.enter.prevent="kirimPesan()"
              placeholder="Ketik pesan..."
              rows="1"
              class="flex-1 text-sm text-gray-700 placeholder:text-gray-400 outline-none bg-transparent resize-none max-h-24"
              style="min-height: 24px"
            />
          </div>
          <button
            @click="kirimPesan()"
            :disabled="!inputPesan.trim() || store.loadingKirim"
            class="w-10 h-10 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-200 rounded-xl flex items-center justify-center transition-all flex-shrink-0"
          >
            <span v-if="store.loadingKirim" class="w-4 h-4 border-2 border-white/50 border-t-transparent rounded-full animate-spin"></span>
            <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <BottomNav v-if="!activeChat" active="konsultasi" />
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { useKonsultasiStore } from '../stores/konsultasi'
import BottomNav from '../components/BottomNav.vue'

const store = useKonsultasiStore()

const searchDokter = ref('')
const activeChat = ref(null)   // objek dokter yang sedang dibuka
const activeSesiId = ref(null) // ID sesi konsultasi dari backend
const inputPesan = ref('')
const chatContainer = ref(null)
const showQuickReply = ref(true)
const isTyping = ref(false)

const quickReplies = [
  'Saya demam 2 hari',
  'Sakit kepala terus',
  'Minta rujukan',
  'Tanya hasil lab',
  'Konsultasi obat',
]

const filteredDokterList = computed(() => {
  if (!searchDokter.value) return store.dokterList
  const q = searchDokter.value.toLowerCase()
  return store.dokterList.filter(
    (d) =>
      d.nama.toLowerCase().includes(q) ||
      d.spesialis.toLowerCase().includes(q),
  )
})

onMounted(async () => {
  await store.fetchIndex()
})

async function bukaChat(dokter, sesiIdExisting = null) {
  activeChat.value = dokter
  store.pesanList = []
  showQuickReply.value = true

  // Buka atau buat sesi di backend
  let sesiId = sesiIdExisting
  if (!sesiId) {
    sesiId = await store.bukaAtauBuatSesi(dokter.id)
  }

  if (!sesiId) {
    // Gagal buat sesi
    activeChat.value = null
    return
  }

  activeSesiId.value = sesiId
  await store.fetchPesan(sesiId)
  nextTick(() => scrollBawah())
}

function tutupChat() {
  activeChat.value = null
  activeSesiId.value = null
  store.pesanList = []
  inputPesan.value = ''
  // Refresh list agar riwayat terupdate
  store.fetchIndex()
}

async function kirimPesan(teks = null) {
  const isi = teks || inputPesan.value.trim()
  if (!isi || !activeSesiId.value) return

  inputPesan.value = ''
  showQuickReply.value = false

  // Tampilkan typing indicator sementara
  isTyping.value = true
  nextTick(() => scrollBawah())

  const berhasil = await store.kirimPesan(activeSesiId.value, isi)

  isTyping.value = false

  if (berhasil) {
    nextTick(() => scrollBawah())
  }
}

function scrollBawah() {
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight
  }
}
</script>
