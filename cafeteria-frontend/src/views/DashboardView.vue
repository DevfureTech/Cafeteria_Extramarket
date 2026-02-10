<template>
  <div class="dashboard-page">
    <header class="header">
      <div class="header__inner">

        <div class="header__brand fade-in-left">
          <div class="header__brand-icon">
            <Coffee :size="28" color="#fff" />
          </div>
          <div>
            <h1 class="header__title">Café Artesano</h1>
            <p class="header__subtitle">Sistema de Gestión</p>
          </div>
        </div>

        <div class="header__actions fade-in-right">

          <div class="header__user">
            <p class="header__user-name">{{ currentUser?.nombre_usuario }}</p>
            <p class="header__user-rol">{{ currentUser?.rol?.nombre }}</p>
          </div>
          <button class="btn-logout" @click="handleLogout">
            <LogOut :size="17" color="#fff" />
            <span class="btn-logout__texto">Cerrar Sesión</span>
          </button>

          <button class="btn-hamburger" @click="mobileOpen = !mobileOpen">
            <X      v-if="mobileOpen"  :size="24" color="var(--cafe-oscuro)" />
            <Menu   v-else            :size="24" color="var(--cafe-oscuro)" />
          </button>
        </div>
      </div>
    </header>
    <div class="dashboard-body">

   
      <aside class="sidebar sidebar--desktop fade-in-up">
        <nav class="sidebar__nav">
          <button
            v-for="(item, index) in visibleMenuItems"
            :key="item.path"
            class="sidebar__item"
            :class="{ 'sidebar__item--active': isActive(item.path) }"
            :style="{ animationDelay: index * 0.05 + 's' }"
            @click="router.push(item.path)"
          >
            <component :is="item.icon" :size="20" />
            <span>{{ item.name }}</span>
          </button>
        </nav>
      </aside>

      <main class="main-content fade-in-up">
        <DashboardHome v-if="route.path === '/dashboard'" />
        <router-view   v-else />
      </main>
    </div>
    <transition name="overlay">
      <div
        v-if="mobileOpen"
        class="mobile-overlay"
        @click="mobileOpen = false"
      />
    </transition>
    <transition name="slide">
      <aside
        v-if="mobileOpen"
        class="sidebar sidebar--mobile"
        @click.stop
      >
        <!-- Header del panel móvil -->
        <div class="sidebar__mobile-header">
          <div class="sidebar__mobile-brand">
            <div class="sidebar__mobile-icon">
              <Coffee :size="22" color="#fff" />
            </div>
            <span class="sidebar__mobile-title">Menú</span>
          </div>
          <button class="sidebar__mobile-close" @click="mobileOpen = false">
            <X :size="20" color="var(--cafe-oscuro)" />
          </button>
        </div>

        <!-- Nav móvil -->
        <nav class="sidebar__nav">
          <button
            v-for="item in visibleMenuItems"
            :key="item.path"
            class="sidebar__item"
            :class="{ 'sidebar__item--active': isActive(item.path) }"
            @click="navigateMobile(item.path)"
          >
            <component :is="item.icon" :size="20" />
            <span>{{ item.name }}</span>
          </button>
        </nav>
      </aside>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed }          from 'vue'
import { useRouter, useRoute }    from 'vue-router'
import { useAuthStore }           from '../stores/auth'  // ← Importar
import {
  Coffee, Users, Package,
  ShoppingCart, BarChart3,
  LogOut, Menu, X, Home
} from 'lucide-vue-next'
import DashboardHome from './DashboardHome.vue'

// ── router / route ──────────────────────────────────────────────
const router = useRouter()
const route  = useRoute()

// ── auth store ──────────────────────────────────────────────────
const authStore = useAuthStore()
const currentUser = computed(() => authStore.currentUser)  // ← Usar computed del store

// ── estado móvil ────────────────────────────────────────────────
const mobileOpen = ref(false)

// ── items de menú ───────────────────────────────────────────────
const menuItems = [
  { name: 'Inicio',          path: '/dashboard',            icon: Home },
  { name: 'Usuarios',        path: '/dashboard/usuarios',      icon: Users,        requiredPermission: 'usuarios,leer' },
  { name: 'Productos',       path: '/dashboard/products',   icon: Coffee,       requiredPermission: 'productos,leer' },
  { name: 'Inventario',      path: '/dashboard/inventory',  icon: Package },
  { name: 'Punto de Venta',  path: '/dashboard/pos',        icon: ShoppingCart },
  { name: 'Reportes',        path: '/dashboard/reports',    icon: BarChart3,    requiredPermission: 'reportes,leer' },
]

// ── computed: filtrar por permiso ───────────────────────────────
const visibleMenuItems = computed(() =>
  menuItems.filter(item => authStore.hasPermission(item.requiredPermission))  // ← Usar método del store
)

// ── helpers ─────────────────────────────────────────────────────
function isActive(path) {
  return route.path === path
}

async function handleLogout() {
  await authStore.logout()  // ← Esperar a que el logout termine
  router.push('/login')  // ← Redirigir directamente al login
}

function navigateMobile(path) {
  router.push(path)
  mobileOpen.value = false
}
</script>

