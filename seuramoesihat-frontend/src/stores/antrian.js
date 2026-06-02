import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAntrianStore = defineStore('antrian', {
  state: () => ({
    aktifList: [],   // semua antrian aktif (hari ini & mendatang)
    riwayat: [],
    loading: false,
    loadingBooking: false,
    error: null,
  }),

  getters: {
    // antrian hari ini (untuk live status)
    aktif: (state) => {
      const today = new Date().toISOString().split('T')[0]
      return state.aktifList.find(a => a.tanggal_raw === today) ?? state.aktifList[0] ?? null
    },
  },

  actions: {
    async fetchAktif() {
      this.loading = true
      try {
        const res = await api.get('/antrian')
        this.aktifList = res.data.data ?? []
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal memuat antrian'
        this.aktifList = []
      } finally {
        this.loading = false
      }
    },

    async fetchRiwayat() {
      try {
        const res = await api.get('/antrian/riwayat')
        this.riwayat = res.data.data
      } catch {
        this.riwayat = []
      }
    },

    async fetchStatus(id) {
      try {
        const res = await api.get(`/antrian/${id}/status`)
        return res.data.data
      } catch {
        return null
      }
    },

    async booking(payload) {
      this.loadingBooking = true
      this.error = null
      try {
        const res = await api.post('/booking', payload)
        return res.data.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Booking gagal'
        return null
      } finally {
        this.loadingBooking = false
      }
    },

    async batalkan(id) {
      try {
        await api.delete(`/antrian/${id}`)
        this.aktifList = this.aktifList.filter(a => a.id !== id)
        return true
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal membatalkan antrian'
        return false
      }
    },
  },
})
