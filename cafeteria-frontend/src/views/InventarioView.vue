<template>
  <div class="inventario-view">
    <!-- Header -->
    <div class="inventario-header">
      <div>
        <h1>Gestión de Inventario</h1>
        <p class="valor-total">
          Valor total:
          <strong>{{ formatCurrency(valorTotalInventario) }}</strong>
        </p>
      </div>

      <router-link to="/proveedor/nuevo" class="btn-nuevo-producto">
        <i class="bi bi-plus-lg"></i>Proveedor
      </router-link>
      <router-link to="/productos/nuevo" class="btn-nuevo-producto">
        <i class="bi bi-plus-lg"></i>Producto
      </router-link>
      <router-link to="/inventario/historial" class="btn-nuevo-producto">
        <i class="bi bi-clock-history"></i>Historial
      </router-link>
    </div>

    <!-- Cards -->
    <div class="resumen-cards">
      <div class="resumen-card resumen-card--total">
        <div class="resumen-card-icon">
          <i class="bi bi-box-seam"></i>
        </div>
        <div class="resumen-card-content">
          <h3>{{ totalProductos }}</h3>
          <p>Total Productos</p>
        </div>
      </div>

      <div class="resumen-card resumen-card--bajo">
        <div class="resumen-card-icon">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="resumen-card-content">
          <h3>{{ alertasStock.length }}</h3>
          <p>Stock Bajo</p>
        </div>
      </div>

      <div class="resumen-card resumen-card--vencer">
        <div class="resumen-card-icon">
          <i class="bi bi-clock-history"></i>
        </div>
        <div class="resumen-card-content">
          <h3>{{ alertasVencimiento.length }}</h3>
          <p>Por Vencer (7 días)</p>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="inventario-tabs">
      <button class="tab-item" :class="{ active: tabActiva === 'productos' }" @click="tabActiva = 'productos'">Productos</button>
      <button class="tab-item" :class="{ active: tabActiva === 'entrada' }" @click="tabActiva = 'entrada'">Entrada</button>
      <button class="tab-item" :class="{ active: tabActiva === 'salida' }" @click="tabActiva = 'salida'">Salida</button>
      <button class="tab-item" :class="{ active: tabActiva === 'ajuste' }" @click="tabActiva = 'ajuste'">Ajuste</button>
      <button class="tab-item has-badge" :class="{ active: tabActiva === 'alertas' }" @click="tabActiva = 'alertas'">
        Alertas
        <span class="tab-badge" v-if="totalAlertas > 0">{{ totalAlertas }}</span>
      </button>
    </div>

    <!-- ================= PRODUCTOS ================= -->
    <template v-if="tabActiva === 'productos'">
      <!-- Buscador -->

<div class="filtros-busqueda">
  <div class="filtro-card">
    <div class="filtro-icon">
      <i class="bi bi-funnel"></i>
    </div>
    <div class="filtro-input-wrap">
      <input
        v-model="filtros.busqueda"
        type="text"
        class="filtro-input"
        placeholder="Buscar por nombre o código..."
      />
    </div>
  </div>
</div>

      <!-- Categorías -->
      <div class="categoria-filtros">
        <button
          class="categoria-btn"
          :class="{ active: !categoriaActiva }"
          @click="filtrarCategoria(null)"
        >
          Todos
        </button>

        <button
          v-for="cat in categorias"
          :key="cat.categoria_id"
          class="categoria-btn"
          :class="{ active: categoriaActiva === cat.categoria_id }"
          @click="filtrarCategoria(cat.categoria_id)"
        >
          {{ cat.nombre }}
        </button>
      </div>

      <!-- Tabla -->
      <div class="productos-tabla-container">
        <table class="productos-tabla">
          <thead>
            <tr>
              <th>PRODUCTO</th>
              <th>CATEGORÍA</th>
              <th>STOCK</th>
              <th>PRECIO</th>
              <th>PROVEEDOR</th>
              <th class="text-end">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in productosFiltrados"
              :key="p.producto_id"
              :class="{ 'row-stock-bajo': p.stock_bajo }"
            >
              <td>
                <strong>{{ p.nombre }}</strong>
                <div class="col-codigo">{{ p.codigo }}</div>
                <div class="col-vencimiento" v-if="p.fecha_vencimiento">
                  <i class="bi bi-calendar-x"></i>
                  Vence: {{ p.fecha_vencimiento }}
                </div>
              </td>

              <td>
                <span class="badge badge-categoria">
                  {{ p.categoria_nombre }}
                </span>
              </td>

              <td>
                <strong>{{ p.cantidad_actual }} {{ p.unidad_medida }}</strong>
                <div style="font-size:0.8rem; color:var(--cafe-gris);">
                  Mín: {{ p.stock_minimo }} {{ p.unidad_medida }}
                </div>

                <span v-if="p.stock_bajo" class="badge-stock-bajo">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                  Stock bajo
                </span>
              </td>

              <td>{{ formatCurrency(p.precio_compra) }}</td>
              
              <td>{{ p.proveedor_nombre || '-' }}</td>

              <td>
                <div class="acciones-grupo">
                  <button
                    class="accion-icon icon-entrada"
                    title="Entrada"
                    @click="tabActiva='entrada'; preseleccionarProducto(p.producto_id)"
                  >
                    <i class="bi bi-arrow-up-circle"></i>
                  </button>

                  <button
                    class="accion-icon icon-salida"
                    title="Salida"
                    @click="tabActiva='salida'; preseleccionarProducto(p.producto_id)"
                  >
                    <i class="bi bi-arrow-down-circle"></i>
                  </button>

                  <button
                    class="accion-icon icon-editar"
                    title="Editar"
                    @click="$router.push(`/productos/editar/${p.producto_id}`)"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>

                  <button
                    class="accion-icon icon-eliminar"
                    title="Eliminar"
                    @click="confirmarEliminar(p)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="productosFiltrados.length === 0" class="text-center py-4">
          <p class="text-muted">No hay productos con esos filtros</p>
        </div>
      </div>
    </template>

    <!-- Otros tabs -->
    <template v-else-if="tabActiva === 'entrada'">
      <EntradaForm :producto-preseleccionado="productoPreseleccionado" @registrado="onMovimientoRegistrado" />
    </template>

    <template v-else-if="tabActiva === 'salida'">
      <SalidaForm :producto-preseleccionado="productoPreseleccionado" @registrado="onMovimientoRegistrado" />
    </template>

    <template v-else-if="tabActiva === 'ajuste'">
      <AjusteForm @registrado="onMovimientoRegistrado" />
    </template>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import productoService from '@/services/productoService'
import inventarioService from '@/services/inventarioService'
import EntradaForm from '@/components/Inventario/EntradaForm.vue'
import SalidaForm from '@/components/Inventario/SalidaForm.vue'
import AjusteForm from '@/components/Inventario/AjusteForm.vue'

export default {
  name: 'InventarioView',
  components: { EntradaForm, SalidaForm, AjusteForm },

  setup() {
    const route = useRoute()

    const tabActiva = ref('productos')
    const categoriaActiva = ref(null)
    const filtros = ref({ busqueda: '' })

    const productos = ref([])
    const categorias = ref([])
    const alertasStock = ref([])
    const alertasVencimiento = ref([])
    const totalProductos = ref(0)
    const valorTotalInventario = ref(0)
    const productoPreseleccionado = ref(null)

    const productosFiltrados = computed(() => {
      let lista = productos.value

      if (categoriaActiva.value) {
        lista = lista.filter(p => p.categoria_id === categoriaActiva.value)
      }

      if (filtros.value.busqueda) {
        const t = filtros.value.busqueda.toLowerCase()
        lista = lista.filter(
          p =>
            p.nombre?.toLowerCase().includes(t) ||
            p.codigo?.toLowerCase().includes(t)
        )
      }

      return lista
    })

    const totalAlertas = computed(
      () => alertasStock.value.length + alertasVencimiento.value.length
    )

    onMounted(async () => {
      if (route.query.tab) tabActiva.value = route.query.tab
      await cargarTodo()
    })

    const cargarTodo = async () => {
      const [prods, cats, stock, vence, resumen] = await Promise.all([
        productoService.getProductos(),
        productoService.getCategorias(),
        productoService.getAlertasStock(),
        productoService.getAlertasVencimiento(),
        inventarioService.getResumen()
      ])

      productos.value = prods
      categorias.value = cats
      alertasStock.value = stock
      alertasVencimiento.value = vence
      totalProductos.value = resumen.totalProductos
      valorTotalInventario.value = resumen.valorTotal
    }

    const filtrarCategoria = id => (categoriaActiva.value = id)
    const preseleccionarProducto = id => (productoPreseleccionado.value = id)
    const onMovimientoRegistrado = async () => await cargarTodo()

    const confirmarEliminar = async p => {
      if (confirm(`¿Eliminar "${p.nombre}"?`)) {
        await productoService.eliminarProducto(p.producto_id)
        await cargarTodo()
      }
    }

    const formatCurrency = v =>
      new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
      }).format(v)

    return {
      tabActiva,
      categoriaActiva,
      filtros,
      productos,
      categorias,
      productosFiltrados,
      alertasStock,
      alertasVencimiento,
      totalProductos,
      valorTotalInventario,
      totalAlertas,
      productoPreseleccionado,
      filtrarCategoria,
      preseleccionarProducto,
      onMovimientoRegistrado,
      confirmarEliminar,
      formatCurrency
    }
  }
}
</script>
