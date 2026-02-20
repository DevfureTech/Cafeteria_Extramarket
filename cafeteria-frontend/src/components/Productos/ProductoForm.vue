<template>
  <div class="producto-form">
    <div class="inventario-header">
      <h1>{{ tituloFormulario }}</h1>
      <button 
        class="btn-nuevo-producto" 
        style="background: var(--cafe-gris);" 
        @click="cancelar"
        type="button"
      >
        <i class="bi bi-x-lg"></i> Cancelar
      </button>
    </div>

    <div class="card">
      <div class="card-body">
        <!-- Mostrar errores generales -->
        <div v-if="errorGeneral" class="alert alert-danger mb-3">
          <i class="bi bi-exclamation-circle me-2"></i>
          {{ errorGeneral }}
        </div>

        <form @submit.prevent="guardarProducto">
          <div class="row g-3">
            <!-- Código (RF-INV-001) -->
            <div class="col-md-4">
              <label class="form-label">Código *</label>
              <input 
                v-model="form.codigo" 
                type="text" 
                class="form-control"
                placeholder="INS001" 
                :class="{ 'is-invalid': errores.codigo }"
                :disabled="modo === 'editar'"
                required
              />
              <div class="invalid-feedback">{{ errores.codigo }}</div>
            </div>

            <!-- Nombre (RF-INV-001) -->
            <div class="col-md-8">
              <label class="form-label">Nombre del Producto *</label>
              <input 
                v-model="form.nombre" 
                type="text" 
                class="form-control"
                placeholder="Café en Grano Arábica" 
                :class="{ 'is-invalid': errores.nombre }"
                required
              />
              <div class="invalid-feedback">{{ errores.nombre }}</div>
            </div>

            <!-- Categoría (RF-INV-002) -->
            <div class="col-md-6">
              <label class="form-label">Categoría *</label>
              <select 
                v-model="form.categoria_id" 
                class="form-select"
                :class="{ 'is-invalid': errores.categoria_id }"
                required
              >
                <option value="">-- Seleccionar categoría --</option>
                <option 
                  v-for="categoria in categorias" 
                  :key="categoria.categoria_id" 
                  :value="categoria.categoria_id"
                >
                  {{ categoria.nombre }}
                </option>
              </select>
              <div class="invalid-feedback">{{ errores.categoria_id }}</div>
            </div>

            <!-- Unidad de medida (RF-INV-001) -->
            <div class="col-md-6">
              <label class="form-label">Unidad de Medida *</label>
              <select 
                v-model="form.unidad_medida" 
                class="form-select"
                :class="{ 'is-invalid': errores.unidad_medida }"
                required
              >
                <option value="">-- Seleccionar unidad --</option>
                <option value="kg">Kilogramos (kg)</option>
                <option value="g">Gramos (g)</option>
                <option value="litros">Litros</option>
                <option value="ml">Mililitros (ml)</option>
                <option value="piezas">Piezas</option>
                <option value="paquetes">Paquetes</option>
                <option value="cajas">Cajas</option>
              </select>
              <div class="invalid-feedback">{{ errores.unidad_medida }}</div>
            </div>

            <!-- Cantidad actual (RF-INV-001) -->
            <div class="col-md-4">
              <label class="form-label">Cantidad Actual *</label>
              <input 
                v-model.number="form.cantidad_actual" 
                type="number" 
                min="0" 
                step="0.01"
                class="form-control" 
                :class="{ 'is-invalid': errores.cantidad_actual }"
                required
              />
              <div class="invalid-feedback">{{ errores.cantidad_actual }}</div>
            </div>

            <!-- Stock mínimo (RF-INV-001, RF-INV-005) -->
            <div class="col-md-4">
              <label class="form-label">Stock Mínimo *</label>
              <input 
                v-model.number="form.stock_minimo" 
                type="number" 
                min="0" 
                step="0.01"
                class="form-control" 
                :class="{ 'is-invalid': errores.stock_minimo }"
                required
              />
              <div class="form-text">
                Se generará alerta al alcanzar este nivel
              </div>
              <div class="invalid-feedback">{{ errores.stock_minimo }}</div>
            </div>

            <!-- Precio de compra (RF-INV-001) -->
            <div class="col-md-4">
              <label class="form-label">Precio de Compra *</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input 
                  v-model.number="form.precio_compra" 
                  type="number" 
                  min="0" 
                  step="0.01"
                  class="form-control" 
                  :class="{ 'is-invalid': errores.precio_compra }"
                  required
                />
              </div>
              <div class="invalid-feedback">{{ errores.precio_compra }}</div>
            </div>

            <!-- Proveedor (RF-INV-001) -->
            <div class="col-md-6">
              <label class="form-label">Proveedor *</label>
              <select
                v-model="form.proveedor_id"
                class="form-select"
                :class="{ 'is-invalid': errores.proveedor_id }"
                required
              >
                <option value="">-- Seleccionar proveedor --</option>
                <option
                  v-for="prov in proveedores"
                  :key="prov.proveedor_id"
                  :value="prov.proveedor_id"
                >
                  {{ prov.nombre }}
                </option>
              </select>
              <div class="invalid-feedback">{{ errores.proveedor_id }}</div>
            </div>



            <!-- Fecha de vencimiento (RF-INV-009) -->
            <div class="col-md-6">
              <label class="form-label">
                Fecha de Vencimiento
                <small class="text-muted">(solo para productos perecederos)</small>
              </label>
              <input 
                v-model="form.fecha_vencimiento" 
                type="date" 
                class="form-control" 
              />
              <div class="form-text">
                Se alertará 7 días antes del vencimiento
              </div>
            </div>
          </div>

          <!-- Mensaje Exito  -->
          <div v-if="mensajeExito" class="alert alert-success mt-3">
  {{ mensajeExito }}
          </div>

          <!-- Botones de acción -->
          <div class="d-flex gap-2 mt-4">
            <button 
              type="submit" 
              class="btn-nuevo-producto" 
              :disabled="cargando"
            >
              <span v-if="cargando">
                <i class="bi bi-hourglass-split"></i> 
                {{ modo === 'crear' ? 'Guardando...' : 'Actualizando...' }}
              </span>
              <span v-else>
                <i class="bi bi-check-lg"></i> 
                {{ modo === 'crear' ? 'Guardar Producto' : 'Actualizar Producto' }}
              </span>
            </button>
            <button 
              type="button"
              class="btn-nuevo-producto" 
              style="background: var(--cafe-gris);"
              @click="cancelar"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import productoService from '@/services/productoService'
import api from '@/services/api'
const proveedores = ref([])

const mensajeExito = ref('')
const mensajeError = ref('')

// Props
const props = defineProps({
  productoId: { 
    type: [Number, String], 
    default: null 
  },
  modo: { 
    type: String, 
    default: 'crear',
    validator: (value) => ['crear', 'editar'].includes(value)
  },
  productoEditado: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['guardado', 'cancelar'])

// State
const cargando = ref(false)
const categorias = ref([])
const errores = reactive({})
const errorGeneral = ref('')

// Form state - nombres de campos según la base de datos
const form = reactive({
  codigo: '',
  nombre: '',
  categoria_id: '',
  cantidad_actual: 0,
  unidad_medida: '',
  precio_compra: 0,
  proveedor_id: '',
  stock_minimo: 0,
  fecha_vencimiento: ''
})

// Computed
const tituloFormulario = computed(() => 
  props.modo === 'crear' ? 'Nuevo Producto' : 'Editar Producto'
)

// Lifecycle
onMounted(async () => {
  await cargarCategorias()
  await cargarProveedores()
  await cargarDatosProducto()
})

// Methods
const cargarCategorias = async () => {
  try {
    const response = await api.get('/categorias')
    categorias.value = response.data
    console.log('Categorías cargadas:', categorias.value)
  } catch (error) {
    console.error('Error cargando categorías:', error)
    errorGeneral.value = 'Error al cargar las categorías'
  }
}

const cargarProveedores = async () => {
  try {
    const response = await api.get('/proveedores')
    proveedores.value = response.data
    console.log('Proveedores cargados:', proveedores.value)
  } catch (error) {
    console.error('Error cargando proveedores:', error)
    errorGeneral.value = 'Error al cargar los proveedores'
  }
}


const cargarDatosProducto = async () => {
  // Si es edición por ID
  if (props.modo === 'editar' && props.productoId) {
    try {
      const response = await api.get(`/productos/${props.productoId}`)
      const producto = response.data
      asignarDatosProducto(producto)
    } catch (error) {
      console.error('Error cargando producto:', error)
      errorGeneral.value = 'Error al cargar los datos del producto'
    }
  }
  
  // Si se pasó producto directamente (para compatibilidad)
  if (props.productoEditado) {
    asignarDatosProducto(props.productoEditado)
  }
}

const asignarDatosProducto = (producto) => {
  Object.assign(form, {
    codigo: producto.codigo || '',
    nombre: producto.nombre || '',
    categoria_id: producto.categoria_id || '',
    cantidad_actual: producto.cantidad_actual || 0,
    unidad_medida: producto.unidad_medida || '',
    precio_compra: producto.precio_compra || 0,
    proveedor_id: producto.proveedor_id || '',
    stock_minimo: producto.stock_minimo || 0,
    fecha_vencimiento: producto.fecha_vencimiento ? 
      new Date(producto.fecha_vencimiento).toISOString().split('T')[0] : ''
  })
}

const validarFormulario = () => {
  // Limpiar errores anteriores
  Object.keys(errores).forEach(key => delete errores[key])
  errorGeneral.value = ''
  
  // Validaciones
  if (!form.codigo?.trim()) {
    errores.codigo = 'El código es obligatorio'
  }
  
  if (!form.nombre?.trim()) {
    errores.nombre = 'El nombre es obligatorio'
  }
  
  if (!form.categoria_id) {
    errores.categoria_id = 'Selecciona una categoría'
  }

  if (!form.proveedor_id) {
  errores.proveedor_id = 'Selecciona un proveedor'
  }

  if (!form.unidad_medida) {
    errores.unidad_medida = 'Selecciona una unidad de medida'
  }
  
  if (form.cantidad_actual < 0) {
    errores.cantidad_actual = 'No puede ser negativo'
  }
  
  if (form.stock_minimo < 0) {
    errores.stock_minimo = 'No puede ser negativo'
  }
  
  if (form.precio_compra < 0) {
    errores.precio_compra = 'No puede ser negativo'
  }
  
  return Object.keys(errores).length === 0
}

const guardarProducto = async () => {
  mensajeExito.value = '' // limpiar éxito previo

  if (!validarFormulario()) {
    errorGeneral.value = 'Por favor corrige los errores en el formulario'
    return
  }
  
  cargando.value = true
  errorGeneral.value = ''
  
  try {
    // Preparar payload exactamente como espera la base de datos
    const payload = {
      codigo: form.codigo,
      nombre: form.nombre,
      categoria_id: form.categoria_id,
      cantidad_actual: form.cantidad_actual,
      unidad_medida: form.unidad_medida,
      precio_compra: form.precio_compra,
      proveedor_id: form.proveedor_id,
      stock_minimo: form.stock_minimo,
      fecha_vencimiento: form.fecha_vencimiento || null
    }
    
    console.log('Enviando payload:', payload)
    
    let response
    
    // Guardar según modo
    if (props.modo === 'crear') {
      response = await api.post('/productos', payload)
      console.log('Producto creado:', response.data)
      mensajeExito.value = '✅ Producto guardado correctamente'
    } else {
      const id = props.productoId || props.productoEditado?.producto_id
      response = await api.put(`/productos/${id}`, payload)
      console.log('Producto actualizado:', response.data)
      mensajeExito.value = '✅ Producto actualizado correctamente'
    }

    // 🔥 emitir evento (lo mantengo)
    emit('guardado', response.data)

    // 🚀 opcional: limpiar mensaje después de 3s
    setTimeout(() => {
      mensajeExito.value = ''
    }, 3000)

  } catch (error) {
    console.error('Error completo:', error)
    console.error('Respuesta del servidor:', error.response?.data)
    
    // Manejar errores de validación del backend
    if (error.response?.data?.errors) {
      Object.assign(errores, error.response.data.errors)
    }
    
    // Error general
    errorGeneral.value =
      error.response?.data?.message ||
      error.response?.data?.error ||
      'Error al guardar el producto. Verifica los datos e intenta nuevamente.'
  } finally {
    cargando.value = false
  }
}


const cancelar = () => {
  emit('cancelar')
}
</script>

<style scoped>
.producto-form {
  width: 100%;
}

.producto-form .card {
  border: none;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-top: 1rem;
}

.card-body {
  padding: 28px;
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: var(--cafe-oscuro);
}

.form-control, .form-select {
  border: 1.5px solid var(--cafe-gris-claro);
  border-radius: 8px;
  padding: 0.6rem 0.8rem;
}

.form-control:focus, .form-select:focus {
  border-color: var(--cafe-medio);
  box-shadow: 0 0 0 3px rgba(123,91,69,0.1);
}

.form-text {
  font-size: 0.75rem;
  margin-top: 0.25rem;
  color: var(--cafe-gris);
}

.alert {
  border-radius: 8px;
  border: none;
}

.alert-danger {
  background: #fee2e2;
  color: #991b1b;
}

.btn-nuevo-producto {
  background: var(--cafe-medio);
  color: white;
  border: none;
  padding: 0.7rem 1.8rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.2s;
  font-size: 0.95rem;
}

.btn-nuevo-producto:hover:not(:disabled) {
  background: var(--cafe-oscuro);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(92,61,46,0.25);
}

.btn-nuevo-producto:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.input-group-text {
  background: var(--cafe-crema);
  border: 1.5px solid var(--cafe-gris-claro);
  border-right: none;
  color: var(--cafe-medio);
  font-weight: 600;
}

.is-invalid {
  border-color: #dc2626 !important;
}

.invalid-feedback {
  display: block;
  color: #dc2626;
  font-size: 0.8rem;
  margin-top: 0.25rem;
}
</style>