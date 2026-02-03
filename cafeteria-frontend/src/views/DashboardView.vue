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
            <p class="header__user-name">{{ currentUser?.nombreCompleto }}</p>
            <p class="header__user-rol">{{ currentUser?.rol }}</p>
          </div>>
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
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'

import {
  Coffee,
  Users,
  Package,
  ShoppingCart,
  BarChart3,
  LogOut,
  Menu,
  X,
  Home
} from 'lucide-vue-next'

import DashboardHome from './DashboardHome.vue'
import { useAuthStore } from '@/store/auth'

const router = useRouter()
const route = useRoute()

const mobileOpen = ref(false)

// ── auth ─────────────────────────────────────────────
const authStore = useAuthStore()
const { user } = storeToRefs(authStore)

// ── menú ─────────────────────────────────────────────
const menuItems = [
  { name: 'Inicio', path: '/dashboard', icon: Home },
  {
    name: 'Usuarios',
    path: '/dashboard/users',
    icon: Users,
    requiredRole: ['Administrador']
  },
  {
    name: 'Productos',
    path: '/dashboard/products',
    icon: Coffee,
    requiredRole: ['Administrador', 'Supervisor']
  },
  { name: 'Inventario', path: '/dashboard/inventory', icon: Package },
  { name: 'Punto de Venta', path: '/dashboard/pos', icon: ShoppingCart },
  {
    name: 'Reportes',
    path: '/dashboard/reports',
    icon: BarChart3,
    requiredRole: ['Administrador', 'Supervisor']
  }
]

// ── permisos ─────────────────────────────────────────
function hasPermission(roles) {
  if (!roles) return true
  return roles.includes(user.value?.rol)
}

const visibleMenuItems = computed(() =>
  menuItems.filter(item => hasPermission(item.requiredRole))
)

// ── helpers ──────────────────────────────────────────
function isActive(path) {
  return route.path === path
}

function handleLogout() {
  authStore.logout()
  router.replace('/login')
}

function navigateMobile(path) {
  router.push(path)
  mobileOpen.value = false
}
</script>


<!-- ================================================================
     ESTILOS
     ================================================================ -->
<style scoped>
/* ── variables ─────────────────────────────────────────────── */
:root {
  --cafe-oscuro:      #5C3D2E;
  --cafe-medio:       #6f4e37;
  --cafe-claro:       #8b7355;
  --cafe-crema:       #f5f1ed;
  --cafe-blanco:      #ffffff;
  --cafe-gris:        #B0A49A;
  --cafe-sombra:      rgba(92, 61, 46, 0.12);
  --cafe-sombra-md:   rgba(92, 61, 46, 0.22);

  --sidebar-w:        288px;   /* 72 * 4 px */
  --header-h:         72px;
}

/* ── reset base ────────────────────────────────────────────── */
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0; padding: 0;
}
button { background: none; border: none; cursor: pointer; font: inherit; }

/* ── page root ─────────────────────────────────────────────── */
.dashboard-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #fafafa 0%, #fff 50%, var(--cafe-crema) 100%);
  font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
  display: flex;
  flex-direction: column;
}

/* ══════════════════════════════════════════════════════════════
   HEADER
   ══════════════════════════════════════════════════════════════ */
.header {
  position: sticky;
  top: 0;
  z-index: 40;
  height: var(--header-h);
  background: rgba(255,255,255,0.82);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(92,61,46,0.10);
  box-shadow: 0 2px 8px var(--cafe-sombra);
}
.header__inner {
  max-width: 1200px;
  margin: 0 auto;
  height: 100%;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* Brand */
.header__brand {
  display: flex;
  align-items: center;
  gap: 14px;
}
.header__brand-icon {
  width: 46px; height: 46px;
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 12px var(--cafe-sombra-md);
}
.header__title {
  font-size: 1.25rem;
  font-weight: 700;
  background: linear-gradient(90deg, var(--cafe-medio), var(--cafe-claro));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.2;
}
.header__subtitle {
  font-size: 0.75rem;
  color: var(--cafe-claro);
}

/* Acciones derecha */
.header__actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Usuario */
.header__user {
  text-align: right;
  background: var(--cafe-crema);
  padding: 8px 16px;
  border-radius: 14px;
}
.header__user-name {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--cafe-oscuro);
}
.header__user-rol {
  font-size: 0.73rem;
  color: var(--cafe-claro);
}

/* Botón logout */
.btn-logout {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  color: #fff;
  border-radius: 14px;
  box-shadow: 0 3px 10px rgba(185,28,28,0.30);
  transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
}
.btn-logout:hover {
  background: linear-gradient(135deg, #b91c1c, #991b1b);
  box-shadow: 0 4px 14px rgba(185,28,28,0.40);
}
.btn-logout:active { transform: scale(0.96); }

/* Hamburguesa */
.btn-hamburger {
  display: none;
  padding: 8px;
  border-radius: 12px;
  transition: background 0.2s;
}
.btn-hamburger:hover { background: var(--cafe-crema); }

/* ══════════════════════════════════════════════════════════════
   CUERPO (sidebar desktop + main)
   ══════════════════════════════════════════════════════════════ */
.dashboard-body {
  flex: 1;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  padding: 24px;
  display: flex;
  gap: 24px;
  align-items: flex-start;
}

/* ══════════════════════════════════════════════════════════════
   SIDEBAR COMÚN
   ══════════════════════════════════════════════════════════════ */
.sidebar__nav {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* Item del sidebar */
.sidebar__item {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  padding: 13px 18px;
  border-radius: 14px;
  color: var(--cafe-medio);
  font-weight: 500;
  font-size: 0.95rem;
  transition: background 0.2s, color 0.2s, transform 0.15s, box-shadow 0.2s;
  animation: fadeSlideLeft 0.35s ease both;
}
.sidebar__item:hover {
  background: var(--cafe-crema);
  transform: scale(1.02);
}

/* Activo */
.sidebar__item--active {
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  color: #fff;
  box-shadow: 0 4px 14px var(--cafe-sombra-md);
  transform: scale(1.04);
}
.sidebar__item--active:hover {
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  transform: scale(1.04);
}

/* ══════════════════════════════════════════════════════════════
   SIDEBAR DESKTOP
   ══════════════════════════════════════════════════════════════ */
.sidebar--desktop {
  width: var(--sidebar-w);
  min-width: var(--sidebar-w);
  background: rgba(255,255,255,0.82);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-radius: 20px;
  box-shadow: 0 6px 24px var(--cafe-sombra);
  padding: 18px;
  position: sticky;
  top: calc(var(--header-h) + 24px);
}

/* ══════════════════════════════════════════════════════════════
   SIDEBAR MÓVIL (overlay + panel)
   ══════════════════════════════════════════════════════════════ */
.mobile-overlay {
  position: fixed;
  inset: 0;
  z-index: 49;
  background: rgba(0,0,0,0.45);
  backdrop-filter: blur(3px);
  -webkit-backdrop-filter: blur(3px);
}

.sidebar--mobile {
  position: fixed;
  left: 0; top: 0; bottom: 0;
  width: var(--sidebar-w);
  z-index: 50;
  background: #fff;
  box-shadow: 0 0 40px rgba(0,0,0,0.25);
  padding: 18px;
  overflow-y: auto;
}

/* Header móvil */
.sidebar__mobile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
}
.sidebar__mobile-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}
.sidebar__mobile-icon {
  width: 38px; height: 38px;
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
}
.sidebar__mobile-title {
  font-weight: 700;
  color: var(--cafe-oscuro);
  font-size: 1.05rem;
}
.sidebar__mobile-close {
  padding: 6px;
  border-radius: 10px;
  transition: background 0.2s;
}
.sidebar__mobile-close:hover { background: var(--cafe-crema); }

/* ══════════════════════════════════════════════════════════════
   CONTENIDO PRINCIPAL
   ══════════════════════════════════════════════════════════════ */
.main-content {
  flex: 1;
  min-width: 0;                        /* evita overflow en flex */
  background: rgba(255,255,255,0.82);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-radius: 20px;
  box-shadow: 0 6px 24px var(--cafe-sombra);
  padding: 28px;
  min-height: calc(100vh - 200px);
}

/* ══════════════════════════════════════════════════════════════
   ANIMACIONES CSS
   ══════════════════════════════════════════════════════════════ */

/* -- entrada genérica -- */
@keyframes fadeSlideLeft {
  from { opacity: 0; transform: translateX(-18px); }
  to   { opacity: 1; transform: translateX(0);     }
}
@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0);    }
}
@keyframes fadeSlideRight {
  from { opacity: 0; transform: translateX(18px); }
  to   { opacity: 1; transform: translateX(0);    }
}

.fade-in-left  { animation: fadeSlideLeft  0.4s ease both; }
.fade-in-right { animation: fadeSlideRight 0.4s ease both; }
.fade-in-up    { animation: fadeSlideUp    0.4s ease both; }

/* -- overlay (v-if toggle) -- */
.overlay-enter-active, .overlay-leave-active {
  transition: opacity 0.28s ease;
}
.overlay-enter-from, .overlay-leave-to { opacity: 0; }
.overlay-enter-to, .overlay-leave-from { opacity: 1; }

/* -- slide panel móvil -- */
.slide-enter-active, .slide-leave-active {
  transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
.slide-enter-to, .slide-leave-from { transform: translateX(0);     }

/* ══════════════════════════════════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  /* Ocultar sidebar desktop, mostrar hamburguesa */
  .sidebar--desktop { display: none; }
  .btn-hamburger    { display: flex; }

  /* Ocultar bloque de usuario en header */
  .header__user { display: none; }

  /* Texto del botón logout se oculta, solo queda el icono */
  .btn-logout__texto { display: none; }
  .btn-logout { padding: 9px; }

  .dashboard-body { padding: 16px; }
  .main-content   { min-height: auto; }
}

@media (max-width: 520px) {
  .sidebar--mobile { width: 85vw; }
  .main-content    { padding: 18px; }
}
</style>