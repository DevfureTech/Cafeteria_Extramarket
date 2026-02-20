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
        <!-- Error general -->
        <div v-if="errorGeneral" class="alert alert-danger mb-3">
          <i class="bi bi-exclamation-circle me-2"></i>
          {{ errorGeneral }}
        </div>

        <form @submit.prevent="guardarProveedor">
          <div class="row g-3">

            <!-- RUC -->
            <div class="col-md-4">
              <label class="form-label">RUC *</label>
              <input 
                v-model="form.ruc"
                type="text"
                class="form-control"
                placeholder="20123456789"
                :class="{ 'is-invalid': errores.ruc }"
                :disabled="modo === 'editar'"
                required
              />
              <div class="invalid-feedback">{{ errores.ruc }}</div>
            </div>

            <!-- Nombre -->
            <div class="col-md-8">
              <label class="form-label">Nombre del Proveedor *</label>
              <input 
                v-model="form.nombre"
                type="text"
                class="form-control"
                placeholder="Distribuidora ABC"
                :class="{ 'is-invalid': errores.nombre }"
                required
              />
              <div class="invalid-feedback">{{ errores.nombre }}</div>
            </div>

            <!-- Teléfono -->
            <div class="col-md-4">
              <label class="form-label">Teléfono</label>
              <input 
                v-model="form.telefono"
                type="text"
                class="form-control"
                placeholder="987654321"
                :class="{ 'is-invalid': errores.telefono }"
              />
              <div class="invalid-feedback">{{ errores.telefono }}</div>
            </div>

            <!-- Email -->
            <div class="col-md-4">
              <label class="form-label">Correo Electrónico</label>
              <input 
                v-model="form.email"
                type="email"
                class="form-control"
                placeholder="proveedor@email.com"
                :class="{ 'is-invalid': errores.email }"
              />
              <div class="invalid-feedback">{{ errores.email }}</div>
            </div>

            <!-- Estado -->
            <div class="col-md-4">
              <label class="form-label">Estado *</label>
              <select 
                v-model="form.estado"
                class="form-select"
                :class="{ 'is-invalid': errores.estado }"
                required
              >
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
              <div class="invalid-feedback">{{ errores.estado }}</div>
            </div>

            <!-- Dirección -->
            <div class="col-md-12">
              <label class="form-label">Dirección</label>
              <input 
                v-model="form.direccion"
                type="text"
                class="form-control"
                placeholder="Av. Ejemplo 123"
              />
            </div>

          </div>

          <!-- Éxito -->
          <div v-if="mensajeExito" class="alert alert-success mt-3">
            {{ mensajeExito }}
          </div>

          <!-- Botones -->
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
                {{ modo === 'crear' ? 'Guardar Proveedor' : 'Actualizar Proveedor' }}
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
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'

// Props
const props = defineProps({
  proveedorId: { type: [Number, String], default: null },
  modo: {
    type: String,
    default: 'crear',
    validator: v => ['crear', 'editar'].includes(v)
  },
  proveedorEditado: { type: Object, default: null }
})

// Emits
const emit = defineEmits(['guardado', 'cancelar'])

// State
const cargando = ref(false)
const errores = reactive({})
const errorGeneral = ref('')
const mensajeExito = ref('')

// Form
const form = reactive({
  ruc: '',
  nombre: '',
  telefono: '',
  email: '',
  direccion: '',
  estado: 'activo'
})

// Computed
const tituloFormulario = computed(() =>
  props.modo === 'crear' ? 'Nuevo Proveedor' : 'Editar Proveedor'
)

// Lifecycle
onMounted(() => {
  cargarDatosProveedor()
})

// Methods
const cargarDatosProveedor = async () => {
  if (props.modo === 'editar' && props.proveedorId) {
    try {
      const res = await api.get(`/proveedores/${props.proveedorId}`)
      asignarDatos(res.data)
    } catch (e) {
      errorGeneral.value = 'Error al cargar proveedor'
    }
  }

  if (props.proveedorEditado) {
    asignarDatos(props.proveedorEditado)
  }
}

const asignarDatos = (p) => {
  Object.assign(form, {
    ruc: p.ruc || '',
    nombre: p.nombre || '',
    telefono: p.telefono || '',
    email: p.email || '',
    direccion: p.direccion || '',
    estado: p.estado || 'activo'
  })
}

const validarFormulario = () => {
  Object.keys(errores).forEach(k => delete errores[k])
  errorGeneral.value = ''

  if (!form.ruc?.trim()) errores.ruc = 'El RUC es obligatorio'
  if (!form.nombre?.trim()) errores.nombre = 'El nombre es obligatorio'

  return Object.keys(errores).length === 0
}

const guardarProveedor = async () => {
  mensajeExito.value = ''

  if (!validarFormulario()) {
    errorGeneral.value = 'Corrige los errores del formulario'
    return
  }

  cargando.value = true

  try {
    let response

    if (props.modo === 'crear') {
      response = await api.post('/proveedores', form)
      mensajeExito.value = '✅ Proveedor guardado'
    } else {
      const id = props.proveedorId || props.proveedorEditado?.proveedor_id
      response = await api.put(`/proveedores/${id}`, form)
      mensajeExito.value = '✅ Proveedor actualizado'
    }

    emit('guardado', response.data)

    setTimeout(() => (mensajeExito.value = ''), 3000)
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errores, error.response.data.errors)
    }

    errorGeneral.value =
      error.response?.data?.message ||
      'Error al guardar proveedor'
  } finally {
    cargando.value = false
  }
}

const cancelar = () => emit('cancelar')
</script>