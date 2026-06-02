import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', component: () => import('../views/Home.vue') },
  { path: '/cari-dokter', component: () => import('../views/CariDokter.vue') },
  { path: '/dokter/:id', component: () => import('../views/DetailDokter.vue') },
  {
    path: '/antrian',
    component: () => import('../views/Antrian.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/rekam-medis',
    component: () => import('../views/RekamMedis.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profil',
    component: () => import('../views/Profil.vue'),
    meta: { requiresAuth: true },
  },
  { path: '/login', component: () => import('../views/auth/Login.vue') },
  { path: '/register', component: () => import('../views/auth/Register.vue') },
  {
    path: '/booking/:id',
    component: () => import('../views/Booking.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/konsultasi',
    component: () => import('../views/Konsultasi.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/konsultasi/:id',
    component: () => import('../views/Konsultasi.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/notifikasi',
    component: () => import('../views/Notifikasi.vue'),
    meta: { requiresAuth: true },
  },
  { path: '/lupa-password', component: () => import('../views/auth/LupaPassword.vue') },

  // ─── Admin ────────────────────────────────────────────────────────────────
  {
    path: '/admin',
    component: () => import('../views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '',        component: () => import('../views/admin/Dashboard.vue') },
      { path: 'antrian', component: () => import('../views/admin/AdminAntrian.vue') },
      { path: 'dokter',  component: () => import('../views/admin/AdminDokter.vue') },
      { path: 'pasien',  component: () => import('../views/admin/AdminPasien.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user  = JSON.parse(localStorage.getItem('user') || 'null')

  if (to.meta.requiresAuth && !token) {
    next({ path: '/login', query: { redirect: to.fullPath } })
    return
  }

  if (to.meta.requiresAdmin && user?.role !== 'admin') {
    next({ path: '/' })
    return
  }

  // Cegah admin akses halaman pasien
  if (token && user?.role === 'admin' && to.path === '/') {
    next({ path: '/admin' })
    return
  }

  next()
})

export default router
