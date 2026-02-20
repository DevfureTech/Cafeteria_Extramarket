import api from './api';

export const userService = {

  async getUsers() {
    const response = await api.get('/usuarios');
    return response.data;
  },

  async createUser(userData) {
    const response = await api.post('/usuarios', userData);
    return response.data;
  },

  async updateUser(id, userData) {
    const response = await api.put(`/usuarios/${id}`, userData);
    return response.data;
  },

  async deleteUser(id) {
    const response = await api.delete(`/usuarios/${id}`);
    return response.data;
  }
};
