<template>
  <div class="pos-page">
    
    <!-- Columna Izquierda: Productos -->
    <div class="productos-section">
      
      <!-- Buscador -->
      <div class="search-bar">
        <div class="search-input-wrapper">
          <Search :size="20" class="search-icon" />
          <input 
            v-model="busqueda"
            type="text" 
            placeholder="Buscar producto por nombre o código..."
            class="search-input"
            @input="buscarProductos"
          />
        </div>
      </div>

      <!-- Filtros por categoría -->
      <div class="filtros-categoria">
        <button 
          :class="['filtro-btn', { 'filtro-btn--active': categoriaActiva === 'Todos' }]"
          @click="filtrarPorCategoria('Todos')"
        >
          Todos
        </button>
        <button 
          v-for="categoria in categorias"
          :key="categoria.id"
          :class="['filtro-btn', { 'filtro-btn--active': categoriaActiva === categoria }]"
          @click="filtrarPorCategoria(categoria.id)"
        >
          {{ categoria.nombre }}
        </button>
      </div>

      <!-- Grid de productos -->
      <div v-if="loading" class="loading-state">
        <div class="spinner-large"></div>
        <p>Cargando productos...</p>
      </div>

      <div v-else class="productos-grid">
        <div 
          v-for="producto in productosFiltrados"
          :key="producto.id_producto"
          class="producto-card"
          @click="seleccionarProducto(producto)"
        >
          <div class="producto-imagen">
            <img 
              v-if="producto.imagen" 
              :src="producto.imagen" 
              :alt="producto.nombre"
            />
            <div v-else class="producto-placeholder">
              <Coffee :size="40" />
            </div>
          </div>
          <div class="producto-info">
            <div class="producto-nombre">{{ producto.nombre_producto }}</div>
            <div class="producto-codigo">{{ producto.codigo }}</div>
            <div class="producto-precio">S/. {{ formatNumber(producto.precio_venta) }}</div>
          </div>
        </div>
      </div>

    </div>

    <!-- Columna Derecha: Carrito -->
    <div class="carrito-section">
      
      <div class="carrito-header">
        <ShoppingCart :size="24" />
        <h2>Carrito</h2>
      </div>

      <!-- Carrito vacío -->
      <div v-if="carrito.length === 0" class="carrito-vacio">
        <ShoppingCart :size="64" color="#cbd5e1" />
        <p>El carrito está vacío</p>
      </div>

      <!-- Items del carrito -->
      <div v-else class="carrito-items">
        <div 
          v-for="(item, index) in carrito"
          :key="index"
          class="carrito-item"
        >
          <div class="item-info">
            <div class="item-nombre">{{ item.nombre_producto }}</div>
            <div v-if="item.modificadores && item.modificadores.length > 0" class="item-modificadores">
              {{ item.modificadores.map(m => m.nombre).join(', ') }}
            </div>
            <div class="item-precio">S/. {{ formatNumber(item.precio_venta) }}</div>
          </div>
          
          <div class="item-acciones">
            <div class="cantidad-control">
              <button @click="decrementarCantidad(index)" class="btn-cantidad">
                <Minus :size="14" />
              </button>
              <span class="cantidad">{{ item.cantidad }}</span>
              <button @click="incrementarCantidad(index)" class="btn-cantidad">
                <Plus :size="14" />
              </button>
            </div>
            <button @click="eliminarDelCarrito(index)" class="btn-eliminar">
              <Trash2 :size="18" />
            </button>
          </div>

          <div class="item-subtotal">
            S/. {{ formatNumber(item.subtotal) }}
          </div>
        </div>
      </div>

      <!-- Totales -->
      <div v-if="carrito.length > 0" class="carrito-totales">
        <div class="total-row">
          <span>Subtotal:</span>
          <span>S/. {{ formatNumber(subtotal) }}</span>
        </div>
        <div class="total-row">
          <span>IVA (16%):</span>
          <span>S/. {{ formatNumber(impuesto) }}</span>
        </div>
        <div class="total-row total-row--final">
          <span>Total:</span>
          <span class="total-amount">S/. {{ formatNumber(total) }}</span>
        </div>
      </div>

      <!-- Botón procesar pago -->
      <button 
        v-if="carrito.length > 0"
        class="btn btn--procesar"
        @click="abrirModalPago"
        :disabled="procesando"
      >
        <span v-if="!procesando">Procesar Pago</span>
        <span v-else class="spinner"></span>
      </button>

      <!-- Info ventas del día -->
      <div class="ventas-del-dia">
        <p>Ventas del día: <strong>S/. {{ formatNumber(ventasDelDia) }}</strong></p>
      </div>

    </div>

    <!-- ════════════════════════════════════════════════════════════
         MODAL: Personalizar Producto
         ════════════════════════════════════════════════════════════ -->
    <transition name="modal">
      <div v-if="showModalPersonalizar" class="modal-overlay" @click="cerrarModalPersonalizar">
        <div class="modal-dialog modal-dialog--medium" @click.stop>
          <div class="modal-header">
            <h2>{{ productoSeleccionado?.nombre }}</h2>
            <button class="modal-close" @click="cerrarModalPersonalizar">
              <X :size="20" />
            </button>
          </div>

          <div class="modal-body">
            <p class="modal-subtitle">Personaliza tu pedido</p>

            <!-- Tamaño -->
            <div class="personalizar-section">
              <h3 class="personalizar-title">Tamaño</h3>
              <div class="opciones-lista">
                <label 
                  v-for="tamano in tamanos"
                  :key="tamano.id"
                  class="opcion-item"
                >
                  <input 
                    type="radio" 
                    :value="tamano" 
                    v-model="tamanoSeleccionado"
                    name="tamano"
                  />
                  <span class="opcion-label">{{ tamano.nombre }}</span>
                  <span v-if="tamano.precio_extra > 0" class="opcion-precio">
                    +S/. {{ tamano.precio_extra }}
                  </span>
                </label>
              </div>
            </div>

            <!-- Cantidad -->
            <div class="personalizar-section">
              <h3 class="personalizar-title">Cantidad</h3>
              <div class="cantidad-control cantidad-control--large">
                <button @click="decrementarCantidadModal" class="btn-cantidad">
                  <Minus :size="18" />
                </button>
                <span class="cantidad">{{ cantidadModal }}</span>
                <button @click="incrementarCantidadModal" class="btn-cantidad">
                  <Plus :size="18" />
                </button>
              </div>
            </div>

          </div>

          <div class="modal-footer">
            <button class="btn btn--ghost" @click="cerrarModalPersonalizar">
              Cancelar
            </button>
            <button class="btn btn--primary" @click="agregarAlCarrito">
              Agregar al Carrito
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ════════════════════════════════════════════════════════════
         MODAL: Método de Pago
         ════════════════════════════════════════════════════════════ -->
    <transition name="modal">
      <div v-if="showModalPago" class="modal-overlay" @click="cerrarModalPago">
        <div class="modal-dialog modal-dialog--medium" @click.stop>
          <div class="modal-header">
            <h2>Método de Pago</h2>
            <button class="modal-close" @click="cerrarModalPago">
              <X :size="20" />
            </button>
          </div>

          <div class="modal-body">
            <div class="total-a-pagar">
              <span>Total:</span>
              <span class="total-amount">S/. {{ formatNumber(total) }}</span>
            </div>

            <div class="metodos-pago">
              <label 
                v-for="metodo in metodosPago"
                :key="metodo.id"
                :class="['metodo-pago-item', { 'metodo-pago-item--selected': metodoPagoSeleccionado === metodo.id }]"
                @click="metodoPagoSeleccionado = metodo.id"
              >
                <div class="metodo-icon">
                  <component :is="metodo.icon" :size="24" />
                </div>
                <div class="metodo-info">
                  <div class="metodo-nombre">{{ metodo.nombre }}</div>
                  <div class="metodo-descripcion">{{ metodo.descripcion }}</div>
                </div>
                <div class="metodo-check">
                  <div v-if="metodoPagoSeleccionado === metodo.id" class="check-icon">
                    <Check :size="18" />
                  </div>
                </div>
              </label>
            </div>

          </div>

          <div class="modal-footer">
            <button class="btn btn--ghost" @click="cerrarModalPago">
              Cancelar
            </button>
            <button 
              class="btn btn--primary" 
              @click="procesarVenta"
              :disabled="!metodoPagoSeleccionado || procesando"
            >
              <span v-if="!procesando">Confirmar Venta</span>
              <span v-else class="spinner"></span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ════════════════════════════════════════════════════════════
         MODAL: Venta Exitosa
         ════════════════════════════════════════════════════════════ -->
    <transition name="modal">
      <div v-if="showModalExito" class="modal-overlay" @click="cerrarModalExito">
        <div class="modal-dialog modal-dialog--medium" @click.stop>
          <div class="modal-body modal-body--centered">
            <div class="icono-exito">
              <CheckCircle :size="64" color="#10b981" />
            </div>
            <h2 class="titulo-exito">¡Venta Exitosa!</h2>
            <p class="ticket-numero">Ticket: {{ ventaExitosa?.numero_ticket }}</p>

            <div class="detalles-venta">
              <div class="detalle-row">
                <span>Fecha:</span>
                <span>{{ formatearFecha(ventaExitosa?.fecha) }}</span>
              </div>
              <div class="detalle-row">
                <span>Hora:</span>
                <span>{{ formatearHora(ventaExitosa?.fecha) }}</span>
              </div>
              <div class="detalle-row">
                <span>Método de pago:</span>
                <span>{{ ventaExitosa?.metodo_pago }}</span>
              </div>
              
              <div class="detalle-productos">
                <div 
                  v-for="item in ventaExitosa?.detalles"
                  :key="item.id_detalle"
                  class="detalle-producto"
                >
                  <span>{{ item.cantidad }}x {{ item.nombre_producto }}</span>
                  <span>S/. {{ formatNumber(item.subtotal) }}</span>
                </div>
              </div>

              <div class="detalle-row detalle-row--subtotal">
                <span>Subtotal:</span>
                <span>S/. {{ formatNumber(ventaExitosa?.subtotal) }}</span>
              </div>
              <div class="detalle-row">
                <span>IVA:</span>
                <span>S/. {{ formatNumber(ventaExitosa?.impuesto) }}</span>
              </div>
              <div class="detalle-row detalle-row--total">
                <span>Total:</span>
                <span>S/. {{ formatNumber(ventaExitosa?.total) }}</span>
              </div>
            </div>

          </div>

          <div class="modal-footer modal-footer--centered">
            <button class="btn btn--secondary" @click="imprimirTicket">
              <Printer :size="18" />
              Imprimir
            </button>
            <button class="btn btn--primary" @click="cerrarModalExito">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  Search, 
  ShoppingCart, 
  Coffee, 
  Plus, 
  Minus, 
  Trash2, 
  X,
  CheckCircle,
  Check,
  Printer,
  DollarSign,
  CreditCard,
  Smartphone
} from 'lucide-vue-next'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

// ══════════════════════════════════════════════════════════════
// Estado
// ══════════════════════════════════════════════════════════════
const loading = ref(false)
const procesando = ref(false)
const productos = ref([])
const categorias = ref([])
const categoriaActiva = ref('Todos')
const busqueda = ref('')
const carrito = ref([])
const ventasDelDia = ref(0)

// Modales
const showModalPersonalizar = ref(false)
const showModalPago = ref(false)
const showModalExito = ref(false)

// Personalización
const productoSeleccionado = ref(null)
const tamanoSeleccionado = ref(null)
const cantidadModal = ref(1)

const tamanos = [
  { id: 'pequeno', nombre: 'Pequeño', precio_extra: 0 },
  { id: 'mediano', nombre: 'Mediano', precio_extra: 10 },
  { id: 'grande', nombre: 'Grande', precio_extra: 15 }
]

// Pago
const metodoPagoSeleccionado = ref(null)
const metodosPago = [
  { 
    id: 'EFECTIVO', 
    nombre: 'Efectivo', 
    descripcion: 'Pago en efectivo',
    icon: DollarSign
  },
  { 
    id: 'TARJETA', 
    nombre: 'Tarjeta', 
    descripcion: 'Débito o crédito',
    icon: CreditCard
  },
  { 
    id: 'TRANSFERENCIA', 
    nombre: 'Transferencia', 
    descripcion: 'Transferencia bancaria',
    icon: Smartphone
  }
]

// Venta exitosa
const ventaExitosa = ref(null)

// ══════════════════════════════════════════════════════════════
// Computed
// ══════════════════════════════════════════════════════════════
const productosFiltrados = computed(() => {
  let filtrados = productos.value

  if (categoriaActiva.value !== 'Todos') {
    filtrados = filtrados.filter(p => p.categoria_id === categoriaActiva.value)
  }

  if (busqueda.value) {
    const termino = busqueda.value.toLowerCase()
    filtrados = filtrados.filter(p => 
      p.nombre_producto.toLowerCase().includes(termino) ||
      p.producto_id.toLowerCase().includes(termino)
    )
  }

  return filtrados
})

const subtotal = computed(() => {
  return carrito.value.reduce((sum, item) => sum + item.subtotal, 0)
})

const impuesto = computed(() => {
  return subtotal.value * 0.18  
})

const total = computed(() => {
  return subtotal.value + impuesto.value
})

// ══════════════════════════════════════════════════════════════
// Métodos
// ══════════════════════════════════════════════════════════════

async function cargarProductos() {
  loading.value = true
  try {
    const response = await api.get('/pos/productos')
    productos.value = response.data.productos

    // Construir categorías únicas con nombre
    const mapa = new Map()

    productos.value.forEach(p => {
      if (!mapa.has(p.categoria_id)) {
        mapa.set(p.categoria_id, {
          id: p.categoria_id,
          nombre: p.categoria_nombre
        })
      }
    })

    categorias.value = Array.from(mapa.values())

  } catch (error) {
    console.error('Error al cargar productos:', error)
    alert('Error al cargar los productos')
  } finally {
    loading.value = false
  }
}
async function cargarVentasDelDia() {
  try {
    const response = await api.get('/pos/ventas/hoy')
    ventasDelDia.value = response.data.resumen.total
  } catch (error) {
    console.error('Error al cargar ventas del día:', error)
  }
}

function filtrarPorCategoria(categoria) {
  categoriaActiva.value = categoria
}

function buscarProductos() {
  // El filtrado se hace automáticamente por el computed productosFiltrados
}

function seleccionarProducto(producto) {
  productoSeleccionado.value = producto
  tamanoSeleccionado.value = tamanos[0] // Pequeño por defecto
  cantidadModal.value = 1
  showModalPersonalizar.value = true
}

function cerrarModalPersonalizar() {
  showModalPersonalizar.value = false
  productoSeleccionado.value = null
  tamanoSeleccionado.value = null
  cantidadModal.value = 1
}

function incrementarCantidadModal() {
  cantidadModal.value++
}

function decrementarCantidadModal() {
  if (cantidadModal.value > 1) {
    cantidadModal.value--
  }
}

function agregarAlCarrito() {
  const precioBase = productoSeleccionado.value.precio_venta
  const precioTamano = tamanoSeleccionado.value.precio_extra
  const precioUnitario = precioBase + precioTamano
  const subtotal = precioUnitario * cantidadModal.value

  const item = {
    id_producto: productoSeleccionado.value.producto_id,
    nombre: productoSeleccionado.value.nombre_producto,
    codigo: productoSeleccionado.value.codigo,
    precio_base: precioBase,
    precio_unitario: precioUnitario,
    cantidad: cantidadModal.value,
    modificadores: [
      {
        tipo: 'tamaño',
        nombre: tamanoSeleccionado.value.nombre,
        precio_extra: tamanoSeleccionado.value.precio_extra
      }
    ],
    subtotal
  }

  carrito.value.push(item)
  cerrarModalPersonalizar()
}

function incrementarCantidad(index) {
  carrito.value[index].cantidad++
  recalcularSubtotal(index)
}

function decrementarCantidad(index) {
  if (carrito.value[index].cantidad > 1) {
    carrito.value[index].cantidad--
    recalcularSubtotal(index)
  }
}

function recalcularSubtotal(index) {
  const item = carrito.value[index]
  item.subtotal = item.precio_unitario * item.cantidad
}

function eliminarDelCarrito(index) {
  if (confirm('¿Eliminar este producto del carrito?')) {
    carrito.value.splice(index, 1)
  }
}

function abrirModalPago() {
  if (carrito.value.length === 0) {
    alert('El carrito está vacío')
    return
  }
  showModalPago.value = true
}

function cerrarModalPago() {
  showModalPago.value = false
  metodoPagoSeleccionado.value = null
}

async function procesarVenta() {
  if (!metodoPagoSeleccionado.value) {
    alert('Seleccione un método de pago')
    return
  }

  procesando.value = true

  try {
    const response = await api.post('/pos/ventas', {
      items: carrito.value,
      metodo_pago: metodoPagoSeleccionado.value,
      subtotal: subtotal.value,
      igv: impuesto.value,
      total: total.value
    })

    ventaExitosa.value = response.data.venta
    showModalPago.value = false
    showModalExito.value = true
    
    // Limpiar carrito
    carrito.value = []
    
    // Recargar ventas del día
    cargarVentasDelDia()

  } catch (error) {
    console.error('Error al procesar venta:', error)
    alert(error.response?.data?.message || 'Error al procesar la venta')
  } finally {
    procesando.value = false
  }
}

function cerrarModalExito() {
  showModalExito.value = false
  ventaExitosa.value = null
}

function imprimirTicket() {
  // Implementar impresión
  window.print()
}

function formatNumber(num) {
  return parseFloat(num || 0).toFixed(2)
}

function formatearFecha(fecha) {
  if (!fecha) return ''
  return new Date(fecha).toLocaleDateString('es-PE')
}

function formatearHora(fecha) {
  if (!fecha) return ''
  return new Date(fecha).toLocaleTimeString('es-PE')
}

// ══════════════════════════════════════════════════════════════
// Lifecycle
// ══════════════════════════════════════════════════════════════
onMounted(() => {
  cargarProductos()
  cargarVentasDelDia()
})
</script>

<style scoped>
/* Variables */
:root {
  --orange-primary:  #f97316;
  --orange-dark:     #ea580c;
}

.pos-page {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 0;
  height: calc(100vh - 80px);
  overflow: hidden;
}

/* ══════════════════════════════════════════════════════════════
   PRODUCTOS SECTION
   ══════════════════════════════════════════════════════════════ */
.productos-section {
  background: #fff;
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.search-bar {
  margin-bottom: 20px;
}

.search-input-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
}

.search-input {
  width: 100%;
  padding: 14px 16px 14px 48px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: orange;
  box-shadow: 0 0 0 3px rgba(237, 105, 10, 0.1);
}

.filtros-categoria {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  overflow-x: auto;
  padding-bottom: 8px;
}

.filtro-btn {
  padding: 8px 16px;
  border-radius: 20px;
  border: 2px solid #e2e8f0;
  background: white;
  font-weight: 600;
  font-size: 0.9rem;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.filtro-btn:hover {
  border-color: rgb(223, 149, 10);
  color:rgba(246, 152, 10, 0.871);
}

.filtro-btn--active {
  background: rgb(223, 149, 10);
  color: white;
  border-color: rgb(223, 149, 10);
}

.productos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
  flex: 1;
}

.producto-card {
  background: #f8fafc;
  border-radius: 16px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid transparent;
}

.producto-card:hover {
  border-color: rgb(214, 144, 15); 
  box-shadow: 0 4px 12px rgba(249,115,22,0.2);
  transform: translateY(-2px);
}

.producto-imagen {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 12px;
  overflow: hidden;
  background: #e2e8f0;
  margin-bottom: 12px;
}

.producto-imagen img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.producto-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.producto-info {
  text-align: center;
}

.producto-nombre {
  font-weight: 600;
  color: #334155;
  font-size: 0.95rem;
  margin-bottom: 4px;
}

.producto-codigo {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-bottom: 8px;
}

.producto-precio {
  font-size: 1.1rem;
  font-weight: 700;
  color: rgb(247, 166, 17);
}

/* ══════════════════════════════════════════════════════════════
   CARRITO SECTION
   ══════════════════════════════════════════════════════════════ */
.carrito-section {
  background: #f8fafc;
  border-left: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.carrito-header {
  padding: 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.carrito-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #334155;
}

.carrito-vacio {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  color: #94a3b8;
}

.carrito-vacio p {
  margin-top: 16px;
  font-size: 1rem;
}

.carrito-items {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.carrito-item {
  background: white;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.item-info {
  margin-bottom: 12px;
}

.item-nombre {
  font-weight: 600;
  color: #334155;
  margin-bottom: 4px;
}

.item-modificadores {
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 4px;
}

.item-precio {
  font-size: 0.9rem;
  color: #94a3b8;
}

.item-acciones {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.cantidad-control {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-cantidad {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 2px solid #e2e8f0;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cantidad:hover {
  border-color: rgb(223, 149, 10);
  background: #fff7ed;
}

.cantidad {
  font-weight: 600;
  font-size: 1rem;
  min-width: 30px;
  text-align: center;
}

.btn-eliminar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: #fee2e2;
  color: #b91c1c;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-eliminar:hover {
  background: #fecaca;
}

.item-subtotal {
  text-align: right;
  font-weight: 700;
  font-size: 1.1rem;
  color: rgb(247, 166, 17);
}

.carrito-totales {
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.95rem;
  color: #64748b;
}

.total-row--final {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid #e2e8f0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #334155;
}

.total-amount {
  color: rgb(247, 166, 17);
  font-size: 1.5rem;
}

.btn--procesar {
  margin: 16px 24px;
  padding: 16px;
  background: rgb(247, 166, 17);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.05rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn--procesar:hover:not(:disabled) {
  background: var(--orange-dark);
  box-shadow: 0 4px 12px rgba(249,115,22,0.3);
}

.btn--procesar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.ventas-del-dia {
  padding: 16px 24px;
  text-align: center;
  font-size: 0.9rem;
  color: #64748b;
  border-top: 1px solid #e2e8f0;
}

/* ══════════════════════════════════════════════════════════════
   MODALES
   ══════════════════════════════════════════════════════════════ */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 999;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-dialog {
  background: #fff;
  border-radius: 20px;
  width: 100%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.4);
}

.modal-dialog--medium {
  max-width: 480px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 28px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #334155;
}

.modal-close {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
  border: none;
  background: transparent;
  color: #64748b;
}

.modal-close:hover {
  background: #f1f5f9;
}

.modal-body {
  padding: 28px;
}

.modal-body--centered {
  text-align: center;
}

.modal-subtitle {
  color: #64748b;
  margin-bottom: 24px;
}

.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding: 20px 28px;
  border-top: 1px solid #e2e8f0;
}

.modal-footer--centered {
  justify-content: center;
}

/* Personalizar */
.personalizar-section {
  margin-bottom: 24px;
}

.personalizar-title {
  font-size: 1rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 12px;
}

.opciones-lista {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.opcion-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.opcion-item:has(input:checked) {
  border-color: rgb(247, 166, 17);
  background: #fff7ed;
}

.opcion-item input {
  margin-right: 12px;
}

.opcion-label {
  flex: 1;
  font-weight: 500;
}

.opcion-precio {
  color: rgb(247, 166, 17);
  font-weight: 600;
}

.cantidad-control--large {
  justify-content: center;
}

.cantidad-control--large .btn-cantidad {
  width: 40px;
  height: 40px;
}

.cantidad-control--large .cantidad {
  font-size: 1.5rem;
  min-width: 50px;
}

/* Método de pago */
.total-a-pagar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: #fff7ed;
  border-radius: 12px;
  margin-bottom: 24px;
}

.total-a-pagar span:first-child {
  font-size: 1rem;
  color: #64748b;
}

.metodos-pago {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.metodo-pago-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.metodo-pago-item--selected {
  border-color: rgb(247, 166, 17);
  background: #fff7ed;
}

.metodo-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
}

.metodo-pago-item--selected .metodo-icon {
  background: rgb(247, 166, 17);
  color: white;
}

.metodo-info {
  flex: 1;
}

.metodo-nombre {
  font-weight: 600;
  color: #334155;
  margin-bottom: 2px;
}

.metodo-descripcion {
  font-size: 0.85rem;
  color: #94a3b8;
}

.metodo-check {
  width: 28px;
  height: 28px;
}

.check-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgb(247, 166, 17);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Venta exitosa */
.icono-exito {
  margin-bottom: 16px;
}

.titulo-exito {
  font-size: 1.8rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 8px;
}

.ticket-numero {
  font-size: 1.1rem;
  color: #64748b;
  margin-bottom: 32px;
}

.detalles-venta {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  text-align: left;
}

.detalle-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.95rem;
  color: #64748b;
}

.detalle-row span:last-child {
  font-weight: 600;
  color: #334155;
}

.detalle-productos {
  margin: 16px 0;
  padding: 16px 0;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
}

.detalle-producto {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.detalle-row--subtotal {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

.detalle-row--total {
  font-size: 1.2rem;
  font-weight: 700;
  color: #334155;
}

.detalle-row--total span:last-child {
  color: rgb(247, 166, 17);
}

/* Botones */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn--primary {
  background: rgb(247, 166, 17);
  color: white;
}

.btn--primary:hover:not(:disabled) {
  background: var(--orange-dark);
}

.btn--secondary {
  background: #64748b;
  color: white;
}

.btn--secondary:hover {
  background: #475569;
}

.btn--ghost {
  background: transparent;
  color: #64748b;
  border: 2px solid #e2e8f0;
}

.btn--ghost:hover {
  background: #f8fafc;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 3px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* Estados */
.loading-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.spinner-large {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top-color: rgb(247, 166, 17);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  margin-bottom: 16px;
}

/* Animaciones */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-dialog,
.modal-leave-active .modal-dialog {
  transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-enter-from .modal-dialog {
  transform: scale(0.92) translateY(-20px);
}

.modal-leave-to .modal-dialog {
  transform: scale(0.92) translateY(20px);
}

/* Responsive */
@media (max-width: 1024px) {
  .pos-page {
    grid-template-columns: 1fr;
    grid-template-rows: 1fr auto;
  }

  .carrito-section {
    border-left: none;
    border-top: 1px solid #e2e8f0;
    max-height: 50vh;
  }
}
</style>