<template>
  <div class="home">

    <!-- Icono central animado -->
    <div class="home__icon-wrap pop-in">
      <Coffee :size="44" color="#fff" />
    </div>

    <!-- Texto de bienvenida -->
    <h2 class="home__titulo fade-in-up" style="animation-delay:.12s">
      ¡Bienvenido, {{ currentUser?.nombre_completo }}!
    </h2>
    <p class="home__subtitulo fade-in-up" style="animation-delay:.22s">
      Selecciona una opción del menú para comenzar
    </p>

    <!-- Grid de acceso rápido -->
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
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'  // ← Importar
import {
  Coffee, Users, Package,
  ShoppingCart, BarChart3
} from 'lucide-vue-next'

const router = useRouter()

// ── auth store ──────────────────────────────────────────
const authStore = useAuthStore()
const currentUser = computed(() => authStore.currentUser)  // ← Del store

// ── cards ───────────────────────────────────────────────
const allCards = [
  { name: 'Usuarios',       path: '/dashboard/users',     icon: Users,        requiredRole: ['Administrador'] },
  { name: 'Productos',      path: '/dashboard/products',  icon: Coffee,       requiredRole: ['Administrador','Supervisor'] },
  { name: 'Inventario',     path: '/dashboard/inventory', icon: Package },
  { name: 'Punto de Venta', path: '/dashboard/pos',       icon: ShoppingCart },
  { name: 'Reportes',       path: '/dashboard/reports',   icon: BarChart3,    requiredRole: ['Administrador','Supervisor'] },
]

const visibleCards = computed(() =>
  allCards.filter(item => authStore.hasPermission(item.requiredRole))  // ← Del store
)
</script>

<style scoped>
/* ── variables (se hereda del padre, pero las redeclaramos por si
       se usa el componente de forma independiente) ─────────────── */
:root {
  --cafe-oscuro:    #5C3D2E;
  --cafe-medio:    #6f4e37;
  --cafe-claro:    #8b7355;
  --cafe-crema:    #f5f1ed;
  --cafe-sombra:   rgba(92, 61, 46, 0.12);
  --cafe-sombra-md: rgba(92,61,46,0.22);
}

/* ── contenedor ────────────────────────────────────────── */
.home {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 48px 24px 56px;
}

/* ── icono central ─────────────────────────────────────── */
.home__icon-wrap {
  width: 84px; height: 84px;
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  border-radius: 22px;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 8px 28px var(--cafe-sombra-md);
  margin-bottom: 28px;
}

/* ── textos ────────────────────────────────────────────── */
.home__titulo {
  font-size: 2rem;
  font-weight: 700;
  color: var(--cafe-oscuro);
  letter-spacing: -0.3px;
  margin-bottom: 8px;
}
.home__subtitulo {
  font-size: 1.05rem;
  color: var(--cafe-claro);
  margin-bottom: 40px;
}

/* ── grid de cards ─────────────────────────────────────── */
.home__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 18px;
  width: 100%;
  max-width: 720px;
}

/* ── card individual ───────────────────────────────────── */
.home__card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 28px 16px;
  background: linear-gradient(145deg, var(--cafe-crema), #fff);
  border: 2px solid rgba(92,61,46,0.08);
  border-radius: 20px;
  cursor: pointer;
  transition: border-color 0.25s, box-shadow 0.25s, transform 0.22s;
}
.home__card:hover {
  border-color: var(--cafe-medio);
  box-shadow: 0 8px 28px var(--cafe-sombra-md);
  transform: translateY(-6px) scale(1.04);
}

/* Icono dentro de la card */
.home__card-icon {
  width: 64px; height: 64px;
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 14px var(--cafe-sombra-md);
  transition: box-shadow 0.25s;
}
.home__card:hover .home__card-icon {
  box-shadow: 0 6px 20px var(--cafe-sombra-md);
}

/* Label */
.home__card-label {
  font-weight: 600;
  color: var(--cafe-oscuro);
  font-size: 1rem;
}

/* ══════════════════════════════════════════════════════════
   ANIMACIONES
   ══════════════════════════════════════════════════════════ */

/* Pop-in (escalar desde 0 con rebote) */
@keyframes popIn {
  0%   { opacity: 0; transform: scale(0);    }
  60%  { transform: scale(1.12); }
  100% { opacity: 1; transform: scale(1);    }
}
.pop-in { animation: popIn 0.45s cubic-bezier(0.34,1.56,0.64,1) both; }

/* Slide up con fade */
@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0);    }
}
.fade-in-up { animation: fadeSlideUp 0.4s ease both; }

/* ── responsive ────────────────────────────────────────── */
@media (max-width: 480px) {
  .home__grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .home__titulo { font-size: 1.5rem; }
}
</style>