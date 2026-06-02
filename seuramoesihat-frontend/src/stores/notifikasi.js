import { defineStore } from 'pinia'
import api from '../api/axios'

export const useNotifikasiStore = defineStore('notifikasi', {
  state: () => ({
    grouped: [],
    totalUnread: 0,
    loading: false,
  }),

  actions: {
    async fetch(kategori = '') {
      this.loading = true
      try {
        const params = kategori ? { kategori } : {}
        const res = await api.get('/notifikasi', { params })
        this.grouped = res.data.data
        this.totalUnread = res.data.total_unread
      } catch {
        this.grouped = []
      } finally {
        this.loading = false
      }
    },

    async baca(id) {
      try {
        await api.patch(`/notifikasi/${id}/baca`)
        // Update state lokal
        this.grouped.forEach((g) => {
          const notif = g.items.find((n) => n.id === id)
          if (notif) {
            notif.dibaca = true
            this.totalUnread = Math.max(0, this.totalUnread - 1)
          }
        })
      } catch {
        // abaikan
      }
    },

    async bacaSemua() {
      try {
        await api.patch('/notifikasi/baca-semua')
        this.grouped.forEach((g) => g.items.forEach((n) => (n.dibaca = true)))
        this.totalUnread = 0
      } catch {
        // abaikan
      }
    },
  },
})
