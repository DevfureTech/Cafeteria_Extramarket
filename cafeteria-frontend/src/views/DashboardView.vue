<template>
  <div class="dashboard-view">
    
    <!-- ════════════════════════════════════════════════════════
         CONTENIDO DE BIENVENIDA (solo cuando estás en /dashboard)
         ════════════════════════════════════════════════════════ -->
    <div v-if="isExactDashboard" class="home-content">
      
      <!-- Icono central animado -->
      <div class="home__icon-wrap pop-in">
        <Coffee :size="44" color="#fff" />
      </div>

      <!-- Texto de bienvenida -->
      <h2 class="home__titulo fade-in-up" style="animation-delay:.12s">
        ¡Bienvenido, {{ currentUser?.nombre_completo || currentUser?.nombre_usuario || 'Usuario' }}!
      </h2>
      <p class="home__subtitulo fade-in-up" style="animation-delay:.22s">
        Selecciona una opción del menú para comenzar
      </p>

      <!-- Grid de tarjetas de acceso rápido -->
      <div class="home__grid">
        <button
          v-for="(item, index) in visibleCards"
          :key="item.path"
          class="home__card fade-in-up"
          :style="{ animationDelay: (0.28 + index * 0.08) + 's' }"
          @click="router.push(item.path)"
        >
          <div class="home__card-icon">
            <component :is="item.icon" :size="32" color="#fff" />
          </div>
          <span class="home__card-label">{{ item.name }}</span>
        </button>
      </div>

    </div>

    <!-- ════════════════════════════════════════════════════════
         RUTAS HIJAS (productos, inventario, etc.)
         ════════════════════════════════════════════════════════ -->
    <router-view v-else />

  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Coffee, Users, Package,
  ShoppingCart, BarChart3
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// ── Usuario actual ──────────────────────────────────────
const currentUser = computed(() => authStore.currentUser)

// ── Detectar si estamos exactamente en /dashboard ──────
const isExactDashboard = computed(() => {
  return route.path === '/dashboard'
})

// ── Tarjetas de acceso rápido ───────────────────────────
const allCards = [
  { 
    name: 'Usuarios',       
    path: '/usuarios',     
    icon: Users,        
    requiredRole: ['Administrador'] 
  },
  { 
    name: 'Productos',      
    path: '/productos',  
    icon: Coffee,       
    requiredRole: ['Administrador', 'Supervisor'] 
  },
  { 
    name: 'Inventario',     
    path: '/inventario', 
    icon: Package,
    requiredRole: null  // Disponible para todos
  },
  { 
    name: 'Punto de Venta', 
    path: '/punto-venta',       
    icon: ShoppingCart,
    requiredRole: null
  },
  { 
    name: 'Reportes',       
    path: '/reportes',   
    icon: BarChart3,    
    requiredRole: ['Administrador', 'Supervisor'] 
  },
]

// Filtrar tarjetas según permisos
const visibleCards = computed(() => {
  return allCards.filter(item => {
    if (!item.requiredRole) return true
    
    if (authStore.hasAnyRole && typeof authStore.hasAnyRole === 'function') {
      return authStore.hasAnyRole(item.requiredRole)
    }
    
    const userRole = authStore.userRole || authStore.currentUser?.rol?.nombre
    return item.requiredRole.includes(userRole)
  })
})

// ── Verificar autenticación al montar ───────────────────
onMounted(() => {
  authStore.checkAuth()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════════════
   DASHBOARD VIEW - Contenedor principal
   ══════════════════════════════════════════════════════════════ */

.dashboard-view {
  width: 100%;
  min-height: 100%;
  animation: fadeIn 0.3s ease both;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}

/* ══════════════════════════════════════════════════════════════
   HOME CONTENT - Bienvenida y tarjetas
   ══════════════════════════════════════════════════════════════ */

.home-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 40px 24px;
  max-width: 900px;
  margin: 0 auto;
}

/* ── Icono central ───────────────────────────────────────── */
.home__icon-wrap {
  width: 84px; 
  height: 84px;
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  border-radius: 22px;
  display: flex; 
  align-items: center; 
  justify-content: center;
  box-shadow: 0 8px 28px var(--cafe-sombra-md);
  margin-bottom: 28px;
}

/* ── Textos ──────────────────────────────────────────────── */
.home__titulo {
  font-size: 1.85rem;
  font-weight: 700;
  color: var(--cafe-oscuro);
  letter-spacing: -0.3px;
  margin-bottom: 8px;
  line-height: 1.2;
}

.home__subtitulo {
  font-size: 1rem;
  color: var(--cafe-gris);
  margin-bottom: 36px;
}

/* ── Grid de tarjetas ────────────────────────────────────── */
.home__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  width: 100%;
}

/* ── Tarjeta individual ──────────────────────────────────── */
.home__card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  padding: 24px 16px;
  background: white;
  border: 1.5px solid var(--cafe-gris-claro);
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 8px var(--cafe-sombra);
}

.home__card:hover {
  border-color: var(--cafe-medio);
  box-shadow: 0 8px 24px var(--cafe-sombra-md);
  transform: translateY(-4px) scale(1.02);
}

.home__card:active {
  transform: translateY(-2px) scale(0.98);
}

/* ── Icono de la tarjeta ─────────────────────────────────── */
.home__card-icon {
  width: 62px; 
  height: 62px;
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  border-radius: 16px;
  display: flex; 
  align-items: center; 
  justify-content: center;
  box-shadow: 0 4px 14px var(--cafe-sombra-md);
  transition: all 0.25s ease;
}

.home__card:hover .home__card-icon {
  box-shadow: 0 6px 20px var(--cafe-sombra-md);
  transform: scale(1.08);
}

/* ── Label de la tarjeta ─────────────────────────────────── */
.home__card-label {
  font-weight: 600;
  color: var(--cafe-oscuro);
  font-size: 0.95rem;
  line-height: 1.3;
}

/* ══════════════════════════════════════════════════════════════
   ANIMACIONES
   ══════════════════════════════════════════════════════════════ */

@keyframes popIn {
  0%   { opacity: 0; transform: scale(0); }
  60%  { transform: scale(1.12); }
  100% { opacity: 1; transform: scale(1); }
}

.pop-in { 
  animation: popIn 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both; 
}

@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.fade-in-up { 
  animation: fadeSlideUp 0.4s ease both; 
}

/* ══════════════════════════════════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════════════════════════════════ */

@media (max-width: 768px) {
  .home-content {
    padding: 32px 20px;
  }

  .home__titulo {
    font-size: 1.5rem;
  }

  .home__subtitulo {
    font-size: 0.9rem;
  }

  .home__grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .home__card {
    padding: 20px 12px;
  }

  .home__card-icon {
    width: 56px;
    height: 56px;
  }

  .home__icon-wrap {
    width: 72px;
    height: 72px;
  }
}

@media (max-width: 480px) {
  .home__grid {
    grid-template-columns: 1fr;
    max-width: 280px;
  }

  .home__card {
    flex-direction: row;
    justify-content: flex-start;
    padding: 16px;
    gap: 16px;
  }

  .home__card-icon {
    width: 48px;
    height: 48px;
  }

  .home__card-label {
    text-align: left;
  }
}
</style>