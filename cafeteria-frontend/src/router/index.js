import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/loginView.vue'
import DashboardView from '@/views/DashboardView.vue'
import UsersView from '@/views/UsersView.vue'
import LandingView from '@/views/LandingPage.vue'

const routes = [
  // ============================================
  // RUTAS PÚBLICAS (sin autenticación)
  // ============================================
  // Landing Page - DEBE SER LA PRIMERA
  {
    path: '/',
    name: 'landing',
    component: LandingView,
    meta: { guest: true, public: true } // Agregamos flag public
  },
  
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true }
  },

  // ============================================
  // RUTAS PROTEGIDAS (requieren autenticación)
  // ============================================
  
  // Dashboard principal (con layout)
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true, roles: null }
  },

  // Usuarios (ruta independiente, no child de dashboard)
  {
    path: '/usuarios',
    name: 'usuarios',
    component: UsersView,
    meta: { requiresAuth: true, roles: ['Administrador'] }
  },

  // ============================================
  // MÓDULO PRODUCTOS
  // ============================================
  {
    path: '/productos',
    name: 'productos',
    component: () => import('@/views/ProductosView.vue'),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor'] }
  },
  {
    path: '/productos/nuevo',
    name: 'nuevo-producto',
    component: () => import('@/components/Productos/ProductoForm.vue'),
    props: { modo: 'crear' },
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor'] }
  },
  {
    path: '/productos/editar/:id',
    name: 'editar-producto',
    component: () => import('@/views/ProductosView.vue'),
    props: (route) => ({ modo: 'editar', productoId: route.params.id }),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor'] }
  },

  // ============================================
  // MÓDULO PROVEEDORES
  // ============================================
  {
    path: '/proveedores',
    name: 'proveedores',
    component: () => import('@/components/Proveedores/ProveedorForm.vue'),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor'] }
  },
  {
    path: '/proveedor/nuevo',
    name: 'nuevo-proveedor',
    component: () => import('@/components/Proveedores/ProveedorForm.vue'),
    props: { modo: 'crear' },
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor'] }
  },
  {
    path: '/proveedor/editar/:id',
    name: 'editar-proveedor',
    component: () => import('@/components/Proveedores/ProveedorForm.vue'),
    props: (route) => ({ modo: 'editar', proveedorId: route.params.id }),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor'] }
  },

  // ============================================
  // MÓDULO INVENTARIO
  // ============================================
  {
    path: '/inventario',
    name: 'inventario',
    component: () => import('@/views/InventarioView.vue'),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor', 'Empleado'] }
  },
  {
    path: '/inventario/historial',
    name: 'historial-inventario',
    component: () => import('@/views/HistorialMovimientos.vue'),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor', 'Empleado'] }
  },
  {
    path: '/inventario/entrada',
    name: 'entrada-inventario',
    component: () => import('@/views/InventarioView.vue'),
    props: { tabInicial: 'entrada' },
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor', 'Empleado'] }
  },
  {
    path: '/inventario/salida',
    name: 'salida-inventario',
    component: () => import('@/views/InventarioView.vue'),
    props: { tabInicial: 'salida' },
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor', 'Empleado'] }
  },
  {
    path: '/inventario/ajuste',
    name: 'ajuste-inventario',
    component: () => import('@/views/InventarioView.vue'),
    props: { tabInicial: 'ajuste' },
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor', 'Empleado'] }
  },

  // ============================================
  // MOVIMIENTOS (Historial)
  // ============================================
  {
    path: '/movimientos',
    name: 'movimientos',
    component: () => import('@/views/InventarioView.vue'),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor', 'Empleado'] }
  },

  // ============================================
  // PUNTO DE VENTA 
  // ============================================
  {
    path: '/punto-venta',
    name: 'punto-venta',
    component: () => import('@/views/POSView.vue'),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor', 'Empleado'] }
  },

  // ============================================
  // REPORTES 
  // ============================================
  {
    path: '/reportes',
    name: 'reportes',
    component: () => import('@/views/ReportesView.vue'),
    meta: { requiresAuth: true, roles: ['Administrador', 'Supervisor'] }
  },

  // ============================================
  // 404 - Página no encontrada
  // ============================================
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    redirect: '/dashboard'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// ============================================
// NAVIGATION GUARDS
// ============================================

/**
 * Verifica si el usuario está autenticado
 * Usa localStorage como fuente de verdad
 */
function isAuthenticated() {
  const token = localStorage.getItem('token')
  const usuario = localStorage.getItem('usuario')
  
  // Validar que ambos existen y el token no esté vacío
  return !!(token && token.trim() !== '' && usuario)
}

/**
 * Obtiene el rol del usuario desde localStorage
 */
function getUserRole() {
  const usuario = localStorage.getItem('usuario')
  if (!usuario) return null
  
  try {
    const userData = JSON.parse(usuario)
    return userData.rol?.nombre || null
  } catch (e) {
    return null
  }
}

/**
 * Verifica si el usuario tiene acceso a la ruta según su rol
 */
function hasRoleAccess(requiredRoles) {
  // Si no requiere roles específicos, permitir
  if (!requiredRoles || requiredRoles === null) return true
  
  const userRole = getUserRole()
  
  // Administrador tiene acceso a todo
  if (userRole === 'Administrador') return true
  
  // Verificar si el rol del usuario está en los roles permitidos
  const rolesArray = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles]
  return rolesArray.includes(userRole)
}

/**
 * Guard principal - se ejecuta antes de cada navegación
 */
router.beforeEach((to, from, next) => {
  const userIsAuthenticated = isAuthenticated()

  // CASO 1: Ruta requiere autenticación
  if (to.meta.requiresAuth) {
    if (!userIsAuthenticated) {
      // No autenticado → redirigir a login
      console.log('🔒 Acceso denegado. Redirigiendo a login...')
      next({
        name: 'login',
        query: { redirect: to.fullPath } // Guardar la ruta destino para volver después del login
      })
    } else {
      // Verificar roles de la ruta
      const requiredRoles = to.meta.roles
      if (!hasRoleAccess(requiredRoles)) {
        console.log('🚫 Acceso denegado. No tienes permisos para esta sección.')
        // Redirigir al dashboard
        next('/dashboard')
        return
      }
      // Autenticado y con roles correctos → permitir acceso
      next()
    }
    return
  }

  // CASO 2: Ruta es para invitados (login)
  if (to.meta.guest) {
    if (userIsAuthenticated) {
      // Ya autenticado → no puede ver login, redirigir a dashboard
      console.log('✅ Usuario ya autenticado. Redirigiendo a dashboard...')
      next('/dashboard')
    } else {
      // No autenticado → puede ver login
      next()
    }
    return
  }

  // CASO 3: Ruta sin restricciones
  next()
})

/**
 * Guard después de cada navegación
 * Útil para analytics, scroll restoration, etc.
 */
router.afterEach((to, from) => {
  // Scroll to top en cada cambio de ruta
  window.scrollTo(0, 0)
  
  // Log para debugging (quitar en producción)
  if (import.meta.env.DEV) {
    console.log(`📍 Navegación: ${from.path} → ${to.path}`)
  }
})

export default router
