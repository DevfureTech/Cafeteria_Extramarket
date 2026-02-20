<template>
  <div class="productos-tabla-container" style="padding: 28px; max-width: 600px;">
    <h2 style="margin-bottom: 24px; color: var(--cafe-oscuro);">
      <i class="bi bi-arrow-down-circle text-warning me-2"></i>
      Registrar Salida
    </h2>

    <!-- Selector de producto -->
    <div class="mb-3">
      <label class="form-label">Producto *</label>
      <select v-model="form.producto_id" class="form-select"
        :class="{ 'is-invalid': errores.producto_id }">
        <option value="">-- Seleccionar producto --</option>
        <option v-for="p in productos" :key="p.id" :value="p.id">
          {{ p.codigo }} - {{ p.nombre }} (Stock: {{ p.cantidad_actual }} {{ p.unidad_medida }})
        </option>
      </select>
      <div class="invalid-feedback">{{ errores.producto_id }}</div>
    </div>

    <!-- Cantidad -->
    <div class="mb-3">
      <label class="form-label">Cantidad a retirar *</label>
      <div class="input-group">
        <input v-model.number="form.cantidad" type="number" min="0.01" step="0.01"
          class="form-control" :class="{ 'is-invalid': errores.cantidad }" />
        <span class="input-group-text">{{ productoSeleccionado?.unidad_medida || '' }}</span>
      </div>
      <div class="invalid-feedback">{{ errores.cantidad }}</div>
    </div>

    <!-- Motivo (RF-INV-004: venta, merma, ajuste) -->
    <div class="mb-3">
      <label class="form-label">Motivo *</label>
      <select v-model="form.motivo" class="form-select"
        :class="{ 'is-invalid': errores.motivo }">
        <option value="">-- Seleccionar motivo --</option>
        <option value="venta">Venta</option>
        <option value="merma">Merma / Desperdicio</option>
        <option value="ajuste">Ajuste</option>
      </select>
      <div class="invalid-feedback">{{ errores.motivo }}</div>
    </div>

    <!-- Descripción adicional -->
    <div class="mb-3">
      <label class="form-label">Descripción (opcional)</label>
      <textarea v-model="form.descripcion" class="form-control" rows="2"
        placeholder="Detalles adicionales sobre la salida..."></textarea>
    </div>

    <!-- Fecha -->
    <div class="mb-4">
      <label class="form-label">Fecha *</label>
      <input v-model="form.fecha" type="date" class="form-control" />
    </div>

    <!-- Alertas -->
    <div v-if="mensajeExito" class="alert alert-success mb-3">
      <i class="bi bi-check-circle me-2"></i> {{ mensajeExito }}
    </div>
    <div v-if="mensajeError" class="alert alert-danger mb-3">
      <i class="bi bi-x-circle me-2"></i> {{ mensajeError }}
    </div>

    <!-- Botones -->
    <div class="d-flex gap-2">
      <button class="btn-nuevo-producto" style="background: linear-gradient(135deg, #f59e0b, #d97706);"
        @click="guardar" :disabled="guardando">
        <span v-if="guardando"><i class="bi bi-hourglass-split"></i> Registrando...</span>
        <span v-else><i class="bi bi-check-lg"></i> Registrar Salida</span>
      </button>
      <button class="btn-nuevo-producto" style="background: var(--cafe-gris);" @click="resetForm">
        <i class="bi bi-arrow-clockwise"></i> Limpiar
      </button>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import productoService from '@/services/productoService'
import inventarioService from '@/services/inventarioService'
import { useRoute } from 'vue-router'

export default {
  name: 'SalidaForm',
  emits: ['registrado'],
  setup(props, { emit }) {
    const route        = useRoute()
    const guardando    = ref(false)
    const productos    = ref([])
    const mensajeExito = ref('')
    const mensajeError = ref('')
    const errores      = reactive({})

    const form = reactive({
      producto_id: route.query.producto || '',
      cantidad: null,
      motivo: '',
      descripcion: '',
      fecha: new Date().toISOString().slice(0, 10),
    })

    const productoSeleccionado = computed(() =>
      productos.value.find(p => p.id == form.producto_id)
    )

    onMounted(async () => {
      productos.value = await productoService.getProductos()
    })

    const validar = () => {
      Object.keys(errores).forEach(k => delete errores[k])
      if (!form.producto_id) errores.producto_id = 'Selecciona un producto'
      if (!form.cantidad || form.cantidad <= 0) errores.cantidad = 'Cantidad debe ser mayor a 0'
      if (!form.motivo)      errores.motivo = 'Selecciona un motivo'
      return Object.keys(errores).length === 0
    }

    const guardar = async () => {
      if (!validar()) return
      guardando.value = true
      mensajeError.value = ''
      mensajeExito.value = ''
      try {
        const result = await inventarioService.registrarSalida(form)
        mensajeExito.value = 'Salida registrada correctamente'
        if (result.alerta_stock) {
          mensajeExito.value += ' ⚠️ Stock por debajo del mínimo'
        }
        emit('registrado')
        setTimeout(resetForm, 2500)
      } catch (error) {
        mensajeError.value = error.response?.data?.error || 'Error al registrar la salida'
      } finally {
        guardando.value = false
      }
    }

    const resetForm = () => {
      form.producto_id = ''
      form.cantidad    = null
      form.motivo      = ''
      form.descripcion = ''
      mensajeExito.value = ''
      mensajeError.value = ''
    }

    return {
      form, errores, guardando, productos, productoSeleccionado,
      mensajeExito, mensajeError, guardar, resetForm,
    }
  }
}
</script>

