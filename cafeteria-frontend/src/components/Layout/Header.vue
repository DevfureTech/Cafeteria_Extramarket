<template>
  <header class="header">
    <div class="header__inner">

      <!-- Brand izquierda -->
      <div class="header__brand fade-in-left">
        <!-- Hamburguesa para móvil (va primero en móvil) -->
        <button class="btn-hamburger" @click="toggleSidebar">
          <X    v-if="mobileOpen"  :size="24" color="var(--cafe-oscuro)" />
          <Menu v-else             :size="24" color="var(--cafe-oscuro)" />
        </button>

        <div class="header__brand-icon">
          <Coffee :size="28" color="#fff" />
        </div>
        <div>
          <h1 class="header__title">Café Artesano</h1>
          <p class="header__subtitle">Sistema de Gestión</p>
        </div>
      </div>

      <!-- Acciones derecha -->
      <div class="header__actions fade-in-right">
        <div class="header__user">
          <p class="header__user-name">{{ currentUser?.nombre_usuario || 'Usuario' }}</p>
          <p class="header__user-rol">{{ currentUser?.rol?.nombre || 'Administrador' }}</p>
        </div>
        <button class="btn-logout" @click="handleLogout">
          <LogOut :size="17" color="#fff" />
          <span class="btn-logout__texto">Cerrar Sesión</span>
        </button>
      </div>

    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Coffee, LogOut, Menu, X } from 'lucide-vue-next'

// Props y emits
const emit = defineEmits(['toggle-sidebar'])

// Router para redirección
const router = useRouter()

// Estado del sidebar móvil
const mobileOpen = ref(false)

// Usuario actual desde localStorage
const currentUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('usuario') || '{}')
  } catch {
    return {}
  }
})

// Toggle del sidebar
const toggleSidebar = () => {
  mobileOpen.value = !mobileOpen.value
  emit('toggle-sidebar')
}

// ✅ Función de logout que limpia localStorage y redirige a login
const handleLogout = () => {
  // Limpiar datos de autenticación
  localStorage.removeItem('token')
  localStorage.removeItem('usuario')
  
  // Opcional: Confirmar antes de salir
  // if (!confirm('¿Estás seguro de cerrar sesión?')) return
  
  // Redirigir a login
  router.push('/login')
  
  // Emitir evento por si el padre necesita hacer algo adicional
  emit('logout')
}
</script>

<style scoped>
/* Los estilos ya vienen de style.css global */
/* Este bloque está vacío o puedes agregarlo si necesitas estilos específicos */
</style>