<template>
  <div class="productos-tabla-container" style="padding: 28px; max-width: 600px;">
    <h2 style="margin-bottom: 24px; color: var(--cafe-oscuro);">
      <i class="bi bi-arrow-up-circle text-success me-2"></i>
      Registrar Entrada
    </h2>

    <!-- Selector de producto -->
    <div class="mb-3">
      <label class="form-label">Producto *</label>
      <select v-model="form.producto_id" class="form-select"
        :class="{ 'is-invalid': errores.producto_id }" @change="onProductoChange">
        <option value="">-- Seleccionar producto --</option>
        <option v-for="p in productos" :key="p.id" :value="p.id">
          {{ p.codigo }} - {{ p.nombre }} (Stock: {{ p.cantidad_actual }} {{ p.unidad_medida }})
        </option>
      </select>
      <div class="invalid-feedback">{{ errores.producto_id }}</div>
    </div>

    <!-- Cantidad -->
    <div class="mb-3">
      <label class="form-label">Cantidad a ingresar *</label>
      <div class="input-group">
        <input v-model.number="form.cantidad" type="number" min="0.01" step="0.01"
          class="form-control" :class="{ 'is-invalid': errores.cantidad }" />
        <span class="input-group-text">{{ productoSeleccionado?.unidad_medida || '' }}</span>
      </div>
      <div class="invalid-feedback">{{ errores.cantidad }}</div>
    </div>

    <!-- Precio unitario (RF-INV-003) -->
    <div class="mb-3">
      <label class="form-label">Precio Unitario *</label>
      <div class="input-group">
        <span class="input-group-text">$</span>
        <input v-model.number="form.precio_unitario" type="number" min="0" step="0.01"
          class="form-control" :class="{ 'is-invalid': errores.precio_unitario }" />
      </div>
      <div class="invalid-feedback">{{ errores.precio_unitario }}</div>
    </div>

    <!-- Proveedor (RF-INV-003) -->
    <div class="mb-3">
      <label class="form-label">Proveedor</label>
      <select v-model="form.proveedor_id" class="form-select">
        <option disabled value="">-- Sin proveedor --</option>
        <option
        v-for="prov in proveedores"
        :key="prov.proveedor_id"
        :value="prov.proveedor_id"
        >
        {{ prov.nombre }}
        </option>

      </select>
    </div>

    <!-- Fecha (RF-INV-003) -->
    <div class="mb-3">
      <label class="form-label">Fecha *</label>
      <input v-model="form.fecha" type="date" class="form-control" />
    </div>

    <!-- Motivo/Notas -->
    <div class="mb-4">
      <label class="form-label">Notas (opcional)</label>
      <textarea v-model="form.motivo" class="form-control" rows="2"
        placeholder="Compra de proveedor, donación, etc."></textarea>
    </div>

    <!-- Alert de éxito -->
    <div v-if="mensajeExito" class="alert alert-success mb-3">
      <i class="bi bi-check-circle me-2"></i> {{ mensajeExito }}
    </div>
    <div v-if="mensajeError" class="alert alert-danger mb-3">
      <i class="bi bi-x-circle me-2"></i> {{ mensajeError }}
    </div>

    <!-- Botones -->
    <div class="d-flex gap-2">
      <button class="btn-nuevo-producto" @click="guardar" :disabled="guardando">
        <span v-if="guardando"><i class="bi bi-hourglass-split"></i> Registrando...</span>
        <span v-else><i class="bi bi-check-lg"></i> Registrar Entrada</span>
      </button>
      <button class="btn-nuevo-producto" style="background: var(--cafe-gris);"
        @click="resetForm">
        <i class="bi bi-arrow-clockwise"></i> Limpiar
      </button>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import productoService from '@/services/productoService'
import inventarioService from '@/services/inventarioService'
import api from '@/services/api'
import { useRoute } from 'vue-router'

export default {
  name: 'EntradaForm',
  emits: ['registrado'],
  setup(props, { emit }) {
    const route       = useRoute()
    const guardando   = ref(false)
    const productos   = ref([])
    const proveedores = ref([])
    const mensajeExito = ref('')
    const mensajeError = ref('')
    const errores      = reactive({})

    const form = reactive({
      producto_id: route.query.producto || '',
      cantidad: null,
      precio_unitario: 0,
      proveedor_id: '',
      fecha: new Date().toISOString().slice(0, 10),
      motivo: '',
    })

    const productoSeleccionado = computed(() =>
      productos.value.find(p => p.id == form.producto_id)
    )

    onMounted(async () => {
      productos.value   = await productoService.getProductos()
      try {
        const { data }  = await api.get('/proveedores')
        proveedores.value = data
      } catch { proveedores.value = [] }

      if (form.producto_id) {
        const p = productoSeleccionado.value
        if (p) form.precio_unitario = p.precio_compra
      }
    })

    const onProductoChange = () => {
      const p = productoSeleccionado.value
      if (p) form.precio_unitario = p.precio_compra
    }

    const validar = () => {
      Object.keys(errores).forEach(k => delete errores[k])
      if (!form.producto_id)      errores.producto_id    = 'Selecciona un producto'
      if (!form.cantidad || form.cantidad <= 0) errores.cantidad = 'Cantidad debe ser mayor a 0'
      if (form.precio_unitario < 0) errores.precio_unitario = 'No puede ser negativo'
      return Object.keys(errores).length === 0
    }

    const guardar = async () => {
      if (!validar()) return
      guardando.value  = true
      mensajeError.value = ''
      mensajeExito.value = ''
      try {
        await inventarioService.registrarEntrada(form)
        mensajeExito.value = 'Entrada registrada correctamente'
        emit('registrado')
        setTimeout(resetForm, 2000)
      } catch (error) {
        mensajeError.value = error.response?.data?.error || 'Error al registrar la entrada'
      } finally {
        guardando.value = false
      }
    }

    const resetForm = () => {
      form.producto_id = ''
      form.cantidad = null
      form.precio_unitario = 0
      form.motivo = ''
      mensajeExito.value = ''
      mensajeError.value = ''
    }

    return {
      form, errores, guardando, productos, proveedores,
      productoSeleccionado, mensajeExito, mensajeError,
      onProductoChange, guardar, resetForm,
    }
  }
}
</script>
