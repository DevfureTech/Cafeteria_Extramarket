<!-- frontend/src/App.vue -->
<template>
  <div id="app">
    <template v-if="isAuthenticated">
      <div class="dashboard-page">

        <!-- ✅ Header fijo arriba -->
        <AppHeader @logout="handleLogout" @toggle-sidebar="sidebarAbierto = !sidebarAbierto" />

        <div class="dashboard-body">
          <Sidebar
          :abierto="sidebarAbierto"
          :menu-items="menuItems"
          @cerrar="sidebarAbierto = false"
        />

          <!-- ✅ Contenido principal — aquí se renderiza InventarioView -->
          <main class="main-content">
            <router-view />
          </main>

        </div>
      </div>
    </template>

    <!-- Sin layout para login -->
    <template v-else>
      <router-view />
    </template>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppHeader from '@/components/Layout/Header.vue'
import Sidebar from '@/components/Layout/Sidebar.vue'

export default {
  name: 'App',
  components: {AppHeader, Sidebar },
  setup() {
    const router = useRouter()
    const auth = useAuthStore()
    const sidebarAbierto = ref(false)
    const menuItems = [
  { nombre: 'Inicio', icono: 'bi-house', ruta: '/dashboard' },
  { nombre: 'Usuarios', icono: 'bi-people', ruta: '/usuarios' },
  { nombre: 'Productos', icono: 'bi-cup-hot', ruta: '/productos' },
  { nombre: 'Inventario', icono: 'bi-box-seam', ruta: '/inventario' },
  { nombre: 'Punto de Venta', icono: 'bi-cart3', ruta: '/punto-venta' },
  { nombre: 'Reportes', icono: 'bi-bar-chart', ruta: '/reportes' },
]

    // 🔥 CLAVE: usar el store, NO localStorage
    const isAuthenticated = computed(() => auth.isAuthenticated)

    const handleLogout = async () => {
      await auth.logout()
      sidebarAbierto.value = false // opcional pero limpio
      router.push('/login')
    }

    return {
      isAuthenticated,
      sidebarAbierto,
      handleLogout,
      menuItems
    }
  }
}
</script>
