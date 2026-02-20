import api from './api'

const productoService = {
  // RF-INV-007: Obtener productos con filtros
  async getProductos(filtros = {}) {
    const params = {}
    if (filtros.nombre)    params.nombre    = filtros.nombre
    if (filtros.codigo)    params.codigo    = filtros.codigo
    if (filtros.categoria) params.categoria = filtros.categoria

    const { data } = await api.get('/productos', { params })
    return data
  },

  // Obtener un producto por ID
  async getProducto(id) {
    const { data } = await api.get(`/productos/${id}`)
    return data
  },

  // RF-INV-001: Crear nuevo producto
  async crearProducto(productoData) {
    const { data } = await api.post('/productos', productoData)
    return data
  },

  // RF-INV-001: Actualizar producto
  async actualizarProducto(id, productoData) {
    const { data } = await api.put(`/productos/${id}`, productoData)
    return data
  },

  // Eliminar producto (baja lógica)
  async eliminarProducto(id) {
    const { data } = await api.delete(`/productos/${id}`)
    return data
  },

  // RF-INV-002: Obtener categorías disponibles
  async getCategorias() {
    const { data } = await api.get('/categorias')
    return data
  },

  // RF-INV-005: Obtener alertas de stock bajo
  async getAlertasStock() {
    const { data } = await api.get('/productos/alertas-stock')
    return data
  },

  // RF-INV-010: Obtener alertas de vencimiento
  async getAlertasVencimiento() {
    const { data } = await api.get('/productos/alertas-vencimiento')
    return data
  },
}

export default productoService