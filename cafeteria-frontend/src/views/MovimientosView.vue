<template>
  <div class="inventario-view">
    <!-- Header -->
    <div class="inventario-header">
      <div>
        <h1>Historial de Movimientos</h1>
        <p class="valor-total">
          Total: <strong>{{ paginacion.total || 0 }}</strong> movimientos
        </p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="categoria-filtros" style="flex-wrap: wrap; gap: 12px; padding: 16px 20px;">
      <select v-model="filtros.tipo" class="form-select" style="max-width: 160px;"
        @change="buscar">
        <option value="">Todos los tipos</option>
        <option value="entrada">Entradas</option>
        <option value="salida">Salidas</option>
        <option value="ajuste">Ajustes</option>
      </select>
      <input v-model="filtros.desde" type="date" class="form-control" style="max-width: 160px;"
        @change="buscar" placeholder="Desde" />
      <input v-model="filtros.hasta" type="date" class="form-control" style="max-width: 160px;"
        @change="buscar" placeholder="Hasta" />
      <button class="categoria-btn active" @click="limpiarFiltros">
        <i class="bi bi-x-circle me-1"></i> Limpiar
      </button>
    </div>

    <!-- Tabla de movimientos -->
    <div class="productos-tabla-container">
      <table class="productos-tabla">
        <thead>
          <tr>
            <th>FECHA Y HORA</th>
            <th>TIPO</th>
            <th>PRODUCTO</th>
            <th>CANTIDAD</th>
            <th>PRECIO U.</th>
            <th>USUARIO</th>
            <th>MOTIVO / JUSTIFICACIÓN</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="mov in movimientos" :key="mov.id">
            <td style="font-size:0.85rem; white-space: nowrap;">
              {{ formatFecha(mov.fecha_movimiento) }}
            </td>
            <td>
              <span :class="badgeClase(mov.tipo)">{{ mov.tipo.toUpperCase() }}</span>
            </td>
            <td>
              <strong>{{ mov.producto_nombre }}</strong>
              <div class="col-codigo">{{ mov.producto_codigo }}</div>
            </td>
            <td :class="cantidadClase(mov.tipo)">
              {{ mov.tipo === 'entrada' ? '+' : mov.tipo === 'salida' ? '-' : '±' }}
              {{ mov.cantidad }} {{ mov.unidad_medida }}
            </td>
            <td>{{ formatCurrency(mov.precio_unitario) }}</td>
            <td>{{ mov.usuario_nombre }}</td>
            <td style="max-width: 250px;">
              <small class="text-muted">
                {{ mov.motivo || '' }}
                {{ mov.justificacion || mov.descripcion || '' }}
              </small>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="movimientos.length === 0" class="text-center py-4">
        <p class="text-muted">No hay movimientos con los filtros seleccionados</p>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="paginacion.last_page > 1"
      class="d-flex justify-content-center gap-2 mt-4">
      <button v-for="page in paginacion.last_page" :key="page"
        class="categoria-btn" :class="{ active: page === paginacion.current_page }"
        @click="irPagina(page)">
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import inventarioService from '@/services/inventarioService'

export default {
  name: 'MovimientosView',
  setup() {
    const route       = useRoute()
    const movimientos = ref([])
    const paginacion  = ref({})
    const filtros     = reactive({ tipo: '', desde: '', hasta: '', producto_id: '' })

    onMounted(async () => {
      if (route.query.producto) filtros.producto_id = route.query.producto
      await cargarMovimientos()
    })

    const cargarMovimientos = async (page = 1) => {
      const params = { ...filtros, per_page: 50, page }
      const result = await inventarioService.getHistorial(params)
      movimientos.value = result.data
      paginacion.value  = {
        total: result.total,
        current_page: result.current_page,
        last_page: result.last_page,
      }
    }

    const buscar = () => cargarMovimientos(1)

    const limpiarFiltros = () => {
      filtros.tipo = ''
      filtros.desde = ''
      filtros.hasta = ''
      cargarMovimientos(1)
    }

    const irPagina = (page) => cargarMovimientos(page)

    const badgeClase = (tipo) => ({
      'badge bg-success': tipo === 'entrada',
      'badge bg-warning text-dark': tipo === 'salida',
      'badge bg-info': tipo === 'ajuste',
    })

    const cantidadClase = (tipo) => ({
      'text-success fw-bold': tipo === 'entrada',
      'text-danger fw-bold': tipo === 'salida',
    })

    const formatFecha = (f) =>
      new Date(f).toLocaleDateString('es-MX', {
        day:'2-digit', month:'2-digit', year:'numeric',
        hour:'2-digit', minute:'2-digit'
      })

    const formatCurrency = (v) =>
      new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v)

    return {
      movimientos, paginacion, filtros,
      buscar, limpiarFiltros, irPagina,
      badgeClase, cantidadClase, formatFecha, formatCurrency,
    }
  }
}
</script>

