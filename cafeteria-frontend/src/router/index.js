import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/loginView.vue'
import DashboardView from '@/views/DashboardView.vue'
import { useAuthStore } from '@/stores/auth'
import UsersView from '@/views/UsersView.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true }
  },
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }, 
    children: [
     
      {
        path: 'usuarios',          
        name: 'usuarios',
        component: UsersView
      },
     
    ]
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Ensure auth state is loaded from localStorage
  authStore.checkAuth()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')  // Redirigir a login si no está autenticado
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/dashboard')  // Redirigir a dashboard si está autenticado y trata de ir a login
  } else {
    next()
  }
})
export default router


