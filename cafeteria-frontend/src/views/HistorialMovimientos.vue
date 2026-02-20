<template>
  <div class="inventario-view">

    <!-- HEADER -->
    <div class="inventario-header">
      <div>
        <h1>Historial de Movimientos</h1>
        <div class="valor-total">
          Registro completo de entradas, salidas y ajustes
        </div>
      </div>
    </div>

    <!-- FILTROS -->
<div class="productos-tabla-container mb-4 filtros-container">
  <div class="row g-3 p-3">

    <div class="col-md-3">
      <label class="filtro-label">Producto</label>
      <select v-model="filtros.producto_id" class="filtro-input">
        <option value="">Todos</option>
        <option
          v-for="p in productos"
          :key="p.producto_id"
          :value="p.producto_id"
        >
          {{ p.nombre }}
        </option>
      </select>
    </div>

    <div class="col-md-2">
      <label class="filtro-label">Tipo</label>
      <select v-model="filtros.tipo" class="filtro-input">
        <option value="">Todos</option>
        <option value="entrada">Entrada</option>
        <option value="salida">Salida</option>
        <option value="ajuste">Ajuste</option>
      </select>
    </div>

    <div class="col-md-2">
      <label class="filtro-label">Desde</label>
      <input type="date" v-model="filtros.desde" class="filtro-input" />
    </div>

    <div class="col-md-2">
      <label class="filtro-label">Hasta</label>
      <input type="date" v-model="filtros.hasta" class="filtro-input" />
    </div>

    <div class="col-md-3 d-flex align-items-end gap-2">
      <button class="btn-nuevo-producto w-100" @click="cargarMovimientos">
        Buscar
      </button>
      <button class="categoria-btn w-100" @click="limpiarFiltros">
        Limpiar
      </button>
    </div>

  </div>

    </div>

    <!-- TABLA -->
    <div class="productos-tabla-container">
      <table class="productos-tabla">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Producto</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Usuario</th>
            <th>Motivo</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="text-center py-4">
              Cargando...
            </td>
          </tr>

          <tr v-else-if="movimientos.length === 0">
            <td colspan="7" class="text-center py-4">
              No hay movimientos
            </td>
          </tr>

          <tr v-for="m in movimientos" :key="m.mov_inv_id">
            <td>{{ m.fecha_movimiento }}</td>

            <td>
              <div class="fw-bold">{{ m.producto_nombre }}</div>
              <div class="col-codigo">{{ m.producto_codigo }}</div>
            </td>

            <td>
              <span
                class="badge"
                :class="{
                  'text-success': m.tipo === 'entrada',
                  'text-danger': m.tipo === 'salida',
                  'text-warning': m.tipo === 'ajuste'
                }"
              >
                {{ m.tipo.toUpperCase() }}
              </span>
            </td>

            <td>{{ m.cantidad }} {{ m.unidad_medida }}</td>

            <td>
              S/ {{ Number(m.precio_unitario ?? 0).toFixed(2) }}
            </td>

            <td>{{ m.usuario_nombre }}</td>

            <td>{{ m.motivo }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- PAGINACIÓN -->
    <div class="d-flex justify-content-between align-items-center mt-2">
      <small>
        Mostrando {{ movimientos.length }} de {{ total }} registros
      </small>

      <div>
        <button
          class="categoria-btn me-2"
          :disabled="pagina === 1"
          @click="pagina--, cargarMovimientos()"
        >
          Anterior
        </button>

        <button
          class="categoria-btn"
          :disabled="pagina === lastPage"
          @click="pagina++, cargarMovimientos()"
        >
          Siguiente
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const movimientos = ref([])
const productos = ref([])
const loading = ref(false)
const pagina = ref(1)
const lastPage = ref(1)
const total = ref(0)

const filtros = ref({
  producto_id: '',
  tipo: '',
  desde: '',
  hasta: ''
})

const cargarProductos = async () => {
  try {
    const { data } = await api.get('/productos')
    productos.value = data.data ?? data
  } catch (e) {
    console.error('Error cargando productos', e)
  }
}

const cargarMovimientos = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/inventario/historial', {
      params: {
        ...filtros.value,
        page: pagina.value
      }
    })

    movimientos.value = data.data
    total.value = data.total
    lastPage.value = data.last_page
  } catch (e) {
    console.error('Error cargando movimientos', e)
  } finally {
    loading.value = false
  }
}

const limpiarFiltros = () => {
  filtros.value = {
    producto_id: '',
    tipo: '',
    desde: '',
    hasta: ''
  }
  pagina.value = 1
  cargarMovimientos()
}

onMounted(async () => {
  await cargarProductos()
  await cargarMovimientos()
})
</script>
