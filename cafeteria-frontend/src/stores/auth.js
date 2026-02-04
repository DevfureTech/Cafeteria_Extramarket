import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  
  // ── Estado ──────────────────────────────────────────────
  const token = ref(localStorage.getItem('token') || null)
  const usuario = ref(JSON.parse(localStorage.getItem('usuario') || 'null'))
  const permisos = ref(JSON.parse(localStorage.getItem('permisos') || '{}'))

  // ── Computed ────────────────────────────────────────────
  const isAuthenticated = computed(() => !!token.value)
  
  const currentUser = computed(() => usuario.value)

  // ── Acciones ────────────────────────────────────────────

  /**
   * Login
   */
  async function login(username, password) {
    try {
      const response = await api.post('/login', { nombre_usuario: username, contraseña_administrador: password, pin: password })

      token.value = response.data.token
      usuario.value = response.data.usuario
      permisos.value = response.data.permisos || {}

      localStorage.setItem('token', response.data.token)
      localStorage.setItem('usuario', JSON.stringify(response.data.usuario))
      localStorage.setItem('permisos', JSON.stringify(response.data.permisos || {}))

      return response.data
    } catch (error) {
      throw error
    }
  }

  /**
   * Logout
   */
  async function logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Error en logout:', error)
    } finally {
      token.value = null
      usuario.value = null
      permisos.value = {}
      localStorage.removeItem('token')
      localStorage.removeItem('usuario')
      localStorage.removeItem('permisos')
    }
  }

/**
 * Verificar permisos
 * @param {string|string[]} requiredPermission - Puede ser 'modulo,accion' o ['Admin', 'Supervisor']
 */
function hasPermission(requiredPermission) {
  // Si no requiere permisos específicos, permitir
  if (!requiredPermission) return true

  // Si no hay usuario, denegar
  if (!usuario.value) return false

  // Admin tiene todos los permisos
  if (usuario.value.rol?.nombre === 'Administrador') return true

  // Si es un string con coma, es un permiso específico (modulo,accion)
  if (typeof requiredPermission === 'string' && requiredPermission.includes(',')) {
    if (!permisos.value) return false
    const [modulo, accion] = requiredPermission.split(',')
    if (!permisos.value[modulo]) return false
    return permisos.value[modulo][accion] || false
  }

  // Si no, es un rol o array de roles
  if (!usuario.value.rol) return false

  const rolesArray = Array.isArray(requiredPermission)
    ? requiredPermission
    : [requiredPermission]

  return rolesArray.includes(usuario.value.rol.nombre)
}

  /**
   * Cargar usuario desde localStorage al iniciar
   */
  function checkAuth() {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('usuario')
    const storedPermisos = localStorage.getItem('permisos')

    if (storedToken && storedUser) {
      token.value = storedToken
      usuario.value = JSON.parse(storedUser)
      permisos.value = JSON.parse(storedPermisos || '{}')
    }
  }

  return {
    // Estado
    token,
    usuario,
    // Computed
    isAuthenticated,
    currentUser,
    // Acciones
    login,
    logout,
    hasPermission,
    checkAuth,
  }
})