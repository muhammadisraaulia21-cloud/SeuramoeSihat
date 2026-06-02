import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    namaUser: (state) => state.user?.nama ?? '',
    roleUser: (state) => state.user?.role ?? 'pasien',
  },

  actions: {
    async login(email, password) {
      this.loading = true
      this.error = null
      try {
        const res = await api.post('/login', { email, password })
        this._simpanSesi(res.data.token, res.data.user)
        return res.data.user.role // kembalikan role untuk redirect
      } catch (err) {
        this.error = err.response?.data?.message || 'Login gagal'
        return false
      } finally {
        this.loading = false
      }
    },

    async register(nama, email, password, noHp = '') {
      this.loading = true
      this.error = null
      try {
        const res = await api.post('/register', { nama, email, password, no_hp: noHp })
        this._simpanSesi(res.data.token, res.data.user)
        return true
      } catch (err) {
        this.error = err.response?.data?.message || 'Registrasi gagal'
        return false
      } finally {
        this.loading = false
      }
    },

    async fetchMe() {
      if (!this.token) return false
      try {
        const res = await api.get('/me')
        this.user = res.data.user
        localStorage.setItem('user', JSON.stringify(this.user))
        return true
      } catch {
        // Token tidak valid — bersihkan state tapi jangan redirect
        // Biarkan router guard yang handle redirect
        this._hapusSesi()
        return false
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch {
        // abaikan error logout
      } finally {
        this._hapusSesi()
      }
    },

    _simpanSesi(token, user) {
      this.token = token
      this.user = user
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
    },

    _hapusSesi() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },
  },
})
