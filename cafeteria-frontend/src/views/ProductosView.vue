<template>
  <div>
    <!-- Modal de formulario de producto -->
    <ProductoForm
      v-if="mostrarFormulario"
      :producto-id="productoEditandoId"
      :modo="modoFormulario"
      @guardado="onProductoGuardado"
      @cancelar="cerrarFormulario"
    />

    <!-- Vista de lista de productos -->
    <div v-else>
      <div class="inventario-header">
        <div>
          <h1>Gestión de Productos</h1>
          <p class="valor-total">
            Total: <strong>{{ productos.length }}</strong> productos
          </p>
        </div>
        <button class="btn-nuevo-producto" @click="abrirFormulario('crear')">
          <i class="bi bi-plus-lg"></i> Nuevo Producto
        </button>
      </div>

      <!-- Filtros RF-INV-007 -->
      <div class="categoria-filtros mb-3">
        <input
          v-model="filtros.nombre"
          type="text"
          placeholder="Buscar por nombre..."
          class="form-control"
          style="max-width: 220px;"
          @input="buscar"
        />
        <input
          v-model="filtros.codigo"
          type="text"
          placeholder="Buscar por código..."
          class="form-control"
          style="max-width: 180px;"
          @input="buscar"
        />
        <button
          v-for="cat in categorias"
          :key="cat.slug"
          class="categoria-btn"
          :class="{ active: filtros.categoria === cat.slug }"
          @click="filtrarCategoria(cat.slug)"
        >
          {{ cat.nombre }}
        </button>
        <button
          class="categoria-btn"
          :class="{ active: !filtros.categoria }"
          @click="filtrarCategoria('')"
        >
          Todos
        </button>
      </div>

      <!-- Tabla de productos -->
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
              v-for="p in productos"
              :key="p.id"
              :class="{ 'row-stock-bajo': p.stock_bajo }"
            >
              <td>
                <strong>{{ p.nombre }}</strong>
                <div class="col-codigo">{{ p.codigo }}</div>
                <div class="col-vencimiento" v-if="p.fecha_vencimiento">
                  <i class="bi bi-calendar-x"></i>
                  Vence: {{ formatFecha(p.fecha_vencimiento) }}
                </div>
              </td>
              <td>
                <span class="badge badge-categoria">{{ p.categoria_nombre }}</span>
              </td>
              <td>
                <strong>{{ p.cantidad_actual }} {{ p.unidad_medida }}</strong>
                <div style="font-size:0.8rem; color: var(--cafe-gris);">
                  Mín: {{ p.stock_minimo }} {{ p.unidad_medida }}
                </div>
                <span v-if="p.stock_bajo" class="badge-stock-bajo">
                  <i class="bi bi-exclamation-triangle-fill"></i> Stock bajo
                </span>
              </td>
              <td>${{ p.precio_compra }}</td>
              <td>{{ p.proveedor_nombre || '-' }}</td>
              <td>
                <div class="acciones-grupo">
                  <button class="accion-icon icon-entrada" title="Registrar entrada"
                    @click="$router.push(`/inventario/entrada?producto=${p.id}`)">
                    <i class="bi bi-arrow-up-circle"></i>
                  </button>
                  <button class="accion-icon icon-salida" title="Registrar salida"
                    @click="$router.push(`/inventario/salida?producto=${p.id}`)">
                    <i class="bi bi-arrow-down-circle"></i>
                  </button>
                  <button class="accion-icon icon-editar" title="Editar"
                    @click="abrirFormulario('editar', p.id)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="accion-icon icon-eliminar" title="Eliminar"
                    @click="confirmarEliminar(p)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="productos.length === 0" class="text-center py-4">
          <p class="text-muted">No se encontraron productos</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import ProductoForm from '@/components/Productos/ProductoForm.vue'
import productoService from '@/services/productoService'

export default {
  name: 'ProductosView',
  components: { ProductoForm },
  setup() {
    const productos           = ref([])
    const categorias          = ref([])
    const mostrarFormulario   = ref(false)
    const modoFormulario      = ref('crear')
    const productoEditandoId  = ref(null)
    const filtros             = ref({ nombre: '', codigo: '', categoria: '' })
    let buscarTimeout         = null

    onMounted(async () => {
      await cargarCategorias()
      await cargarProductos()
    })

    const cargarCategorias = async () => {
      categorias.value = await productoService.getCategorias()
    }

    const cargarProductos = async () => {
      productos.value = await productoService.getProductos(filtros.value)
    }

    const buscar = () => {
      clearTimeout(buscarTimeout)
      buscarTimeout = setTimeout(cargarProductos, 400)
    }

    const filtrarCategoria = async (slug) => {
      filtros.value.categoria = slug
      await cargarProductos()
    }

    const abrirFormulario = (modo, id = null) => {
      modoFormulario.value     = modo
      productoEditandoId.value = id
      mostrarFormulario.value  = true
    }

    const cerrarFormulario = () => {
      mostrarFormulario.value  = false
      productoEditandoId.value = null
    }

    const onProductoGuardado = async () => {
      cerrarFormulario()
      await cargarProductos()
    }

    const confirmarEliminar = async (p) => {
      if (confirm(`¿Eliminar "${p.nombre}"?`)) {
        await productoService.eliminarProducto(p.id)
        await cargarProductos()
      }
    }

    const formatFecha = (fecha) =>
      new Date(fecha).toLocaleDateString('es-MX')

    return {
      productos, categorias, mostrarFormulario, modoFormulario,
      productoEditandoId, filtros, buscar, filtrarCategoria,
      abrirFormulario, cerrarFormulario, onProductoGuardado,
      confirmarEliminar, formatFecha,
    }
  }
}
</script>
