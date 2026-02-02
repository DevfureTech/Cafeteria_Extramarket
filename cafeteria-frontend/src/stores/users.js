import { defineStore } from 'pinia'
import userService from '@/services/userService'

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchUsers() {
      this.loading = true
      this.error = null

      try {
        const response = await userService.getUsers()
        this.users = response
      } catch (e) {
        this.error = 'Error al cargar usuarios'
      } finally {
        this.loading = false
      }
    },

    async createUser(userData) {
      try {
        await userService.createUser(userData)
        await this.fetchUsers()
      } catch (e) {
        this.error = 'Error al crear usuario'
      }
    },

    async updateUser(id, userData) {
      try {
        await userService.updateUser(id, userData)
        await this.fetchUsers()
      } catch (e) {
        this.error = 'Error al actualizar usuario'
      }
    },

    async deleteUser(id) {
      try {
        await userService.deleteUser(id)
        this.users = this.users.filter(u => u.id !== id)
      } catch (e) {
        this.error = 'Error al eliminar usuario'
      }
    }
  }
})
