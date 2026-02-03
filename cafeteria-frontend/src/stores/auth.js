import { defineStore } from 'pinia'
import authService from '@/services/authService'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    isAuthenticated: !!localStorage.getItem('token')
  }),

  actions: {
    async login(credentials) {
      const res = await authService.login(credentials)
      this.token = res.token
      this.user = res.user
      this.isAuthenticated = true
      localStorage.setItem('token', res.token)
    },

    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      localStorage.removeItem('token')
    },

    hasPermission(requiredRole) {
      if (!this.user || !this.user.rol) return false
      if (Array.isArray(requiredRole)) {
        return requiredRole.includes(this.user.rol)
      }
      return this.user.rol === requiredRole
    }, 
    setUser(user) {
      this.currentUser = user
    },
  }
})