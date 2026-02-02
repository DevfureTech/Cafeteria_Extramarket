import api from './api'
import axios from 'axios'

const authService = {
  login(credentials) {
    return api.post('/login', credentials)
  },

  logout() {
    return api.post('/logout')
  },

  getCurrentUser() {
    return api.get('/me')
  }
}

export const login = (data) => {
  return axios.post('http://127.0.0.1:8000/api/login', {
    nombre_usuario: data.nombre_usuario,
    contraseña_administrador: data.contraseña_administrador
  })
}
export default authService

