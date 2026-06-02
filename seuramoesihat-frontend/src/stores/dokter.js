import { defineStore } from 'pinia'
import api from '../api/axios'

export const useDokterStore = defineStore('dokter', {
  state: () => ({
    list: [],
    detail: null,
    jadwal: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchList(params = {}) {
      this.loading = true
      this.error = null
      this.list = []
      try {
        const res = await api.get('/dokter', { params })
        this.list = res.data.data ?? []
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal memuat data dokter'
        console.error('[DokterStore] fetchList error:', err)
      } finally {
        this.loading = false
      }
    },

    async fetchDetail(id) {
      this.loading = true
      this.error = null
      try {
        const res = await api.get(`/dokter/${id}`)
        this.detail = res.data.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Dokter tidak ditemukan'
      } finally {
        this.loading = false
      }
    },

    async fetchJadwal(id) {
      try {
        const res = await api.get(`/dokter/${id}/jadwal`)
        this.jadwal = res.data.data
      } catch {
        this.jadwal = []
      }
    },
  },
})
