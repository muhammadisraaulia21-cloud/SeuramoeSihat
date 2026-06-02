import { defineStore } from 'pinia'
import api from '../api/axios'

export const useKonsultasiStore = defineStore('konsultasi', {
  state: () => ({
    riwayatChat: [],
    dokterList: [],
    pesanList: [],
    loading: false,
    loadingPesan: false,
    loadingKirim: false,
    error: null,
  }),

  actions: {
    async fetchIndex() {
      this.loading = true
      this.error = null
      try {
        const res = await api.get('/konsultasi')
        this.riwayatChat = res.data.riwayat_chat
        this.dokterList = res.data.dokter_list
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal memuat data konsultasi'
      } finally {
        this.loading = false
      }
    },

    async bukaAtauBuatSesi(dokterId) {
      try {
        const res = await api.post('/konsultasi', { dokter_id: dokterId })
        return res.data.data.id
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal membuka sesi konsultasi'
        return null
      }
    },

    async fetchPesan(sesiId) {
      this.loadingPesan = true
      try {
        const res = await api.get(`/konsultasi/${sesiId}/pesan`)
        this.pesanList = res.data.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal memuat pesan'
      } finally {
        this.loadingPesan = false
      }
    },

    async kirimPesan(sesiId, teks) {
      this.loadingKirim = true
      try {
        const res = await api.post(`/konsultasi/${sesiId}/pesan`, { teks })
        const { pesan_pasien, pesan_dokter } = res.data.data
        // Tambahkan pesan pasien dan balasan dokter ke list
        this.pesanList.push(pesan_pasien)
        this.pesanList.push(pesan_dokter)
        return true
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengirim pesan'
        return false
      } finally {
        this.loadingKirim = false
      }
    },
  },
})
