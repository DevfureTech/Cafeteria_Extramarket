import { defineStore } from 'pinia'

import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token
  },

  actions: {
    async login(credentials) {
      const res = await axios.post('http://127.0.0.1:8000/api/login', credentials)

      this.token = res.data.token
      localStorage.setItem('token', this.token)
    },

    logout() {
      this.token = null
      localStorage.removeItem('token')
    }
  }
})
