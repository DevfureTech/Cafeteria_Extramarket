import api from './api'

const inventarioService = {
  // RF-INV-003: Registrar entrada de inventario
  async registrarEntrada(entradaData) {
    const { data } = await api.post('/inventario/entradas', entradaData)
    return data
  },

  // RF-INV-004: Registrar salida de inventario
  async registrarSalida(salidaData) {
    const { data } = await api.post('/inventario/salidas', salidaData)
    return data
  },

  // RF-INV-008: Registrar ajuste manual con justificación
  async registrarAjuste(ajusteData) {
    const { data } = await api.post('/inventario/ajustes', ajusteData)
    return data
  },

  // RF-INV-006: Obtener historial completo de movimientos
  async getHistorial(filtros = {}) {
    const params = {}
    if (filtros.producto_id) params.producto_id = filtros.producto_id
    if (filtros.tipo)        params.tipo        = filtros.tipo
    if (filtros.desde)       params.desde       = filtros.desde
    if (filtros.hasta)       params.hasta       = filtros.hasta
    if (filtros.per_page)    params.per_page    = filtros.per_page

    const { data } = await api.get('/inventario/historial', { params })
    return data
  },

  // Resumen general del inventario
  async getResumen() {
    const { data } = await api.get('/inventario/resumen')
    return data
  },

  // Últimos movimientos para dashboard
  async getUltimosMovimientos(limite = 10) {
    const { data } = await api.get('/inventario/ultimos-movimientos', {
      params: { limite }
    })
    return data
  },
}

export default inventarioService