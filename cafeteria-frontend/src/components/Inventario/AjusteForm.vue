
<template>
  <div class="productos-tabla-container" style="padding: 28px; max-width: 600px;">
    <h2 style="margin-bottom: 24px; color: var(--cafe-oscuro);">
      <i class="bi bi-sliders me-2" style="color: #5bc0de;"></i>
      Ajuste Manual de Inventario
    </h2>

    <!-- Selector de producto -->
    <div class="mb-3">
      <label class="form-label">Producto *</label>
      <select v-model="form.producto_id" class="form-select"
        :class="{ 'is-invalid': errores.producto_id }" @change="cargarStockActual">
        <option value="">-- Seleccionar producto --</option>
        <option v-for="p in productos" :key="p.id" :value="p.id">
          {{ p.codigo }} - {{ p.nombre }} (Stock: {{ p.cantidad_actual }} {{ p.unidad_medida }})
        </option>
      </select>
      <div class="invalid-feedback">{{ errores.producto_id }}</div>
    </div>

    <!-- Stock actual (informativo) -->
    <div v-if="productoSeleccionado" class="alert alert-info mb-3">
      <strong>Stock actual:</strong>
      {{ productoSeleccionado.cantidad_actual }} {{ productoSeleccionado.unidad_medida }}
    </div>

    <!-- Nueva cantidad -->
    <div class="mb-3">
      <label class="form-label">Nueva Cantidad *</label>
      <div class="input-group">
        <input v-model.number="form.cantidad_nueva" type="number" min="0" step="0.01"
          class="form-control" :class="{ 'is-invalid': errores.cantidad_nueva }" />
        <span class="input-group-text">{{ productoSeleccionado?.unidad_medida || '' }}</span>
      </div>
      <!-- Diferencia visual -->
      <small v-if="diferencia !== null" :class="diferencia >= 0 ? 'text-success' : 'text-danger'">
        Diferencia: {{ diferencia >= 0 ? '+' : '' }}{{ diferencia.toFixed(2) }}
        {{ productoSeleccionado?.unidad_medida }}
      </small>
      <div class="invalid-feedback">{{ errores.cantidad_nueva }}</div>
    </div>

    <!-- Justificación OBLIGATORIA (RF-INV-008) -->
    <div class="mb-3">
      <label class="form-label">
        Justificación *
        <span class="text-muted">(mínimo 10 caracteres)</span>
      </label>
      <textarea
        v-model="form.justificacion"
        class="form-control"
        rows="3"
        :class="{ 'is-invalid': errores.justificacion }"
        placeholder="Describe el motivo del ajuste: conteo físico, corrección de error, etc."
      ></textarea>
      <small class="text-muted">{{ form.justificacion.length }}/1000 caracteres</small>
      <div class="invalid-feedback">{{ errores.justificacion }}</div>
    </div>

    <!-- Fecha -->
    <div class="mb-4">
      <label class="form-label">Fecha</label>
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
      <button class="btn-nuevo-producto"
        style="background: linear-gradient(135deg, #5bc0de, #46b8da);"
        @click="guardar" :disabled="guardando">
        <span v-if="guardando"><i class="bi bi-hourglass-split"></i> Ajustando...</span>
        <span v-else><i class="bi bi-check-lg"></i> Aplicar Ajuste</span>
      </button>
      <button class="btn-nuevo-producto" style="background: var(--cafe-gris);" @click="resetForm">
        <i class="bi bi-arrow-clockwise"></i> Limpiar
      </button>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import productoService from '@/services/productoService'
import inventarioService from '@/services/inventarioService'

export default {
  name: 'AjusteForm',
  emits: ['registrado'],
  setup(props, { emit }) {
    const guardando    = ref(false)
    const productos    = ref([])
    const mensajeExito = ref('')
    const mensajeError = ref('')
    const errores      = reactive({})

    const form = reactive({
      producto_id: '',
      cantidad_nueva: null,
      justificacion: '',
      fecha: new Date().toISOString().slice(0, 10),
    })

    const productoSeleccionado = computed(() =>
      productos.value.find(p => p.id == form.producto_id)
    )

    const diferencia = computed(() => {
      if (!productoSeleccionado.value || form.cantidad_nueva === null) return null
      return form.cantidad_nueva - productoSeleccionado.value.cantidad_actual
    })

    onMounted(async () => {
      productos.value = await productoService.getProductos()
    })

    const cargarStockActual = () => {
      // Se actualiza automáticamente con el computed
    }

    const validar = () => {
      Object.keys(errores).forEach(k => delete errores[k])
      if (!form.producto_id)  errores.producto_id  = 'Selecciona un producto'
      if (form.cantidad_nueva === null || form.cantidad_nueva < 0)
        errores.cantidad_nueva = 'Ingresa una cantidad válida (≥ 0)'
      if (!form.justificacion || form.justificacion.length < 10)
        errores.justificacion = 'La justificación debe tener al menos 10 caracteres'
      return Object.keys(errores).length === 0
    }

    const guardar = async () => {
      if (!validar()) return
      guardando.value = true
      mensajeError.value = ''
      mensajeExito.value = ''
      try {
        await inventarioService.registrarAjuste(form)
        mensajeExito.value = 'Ajuste registrado correctamente'
        emit('registrado')
        setTimeout(resetForm, 2000)
      } catch (error) {
        mensajeError.value = error.response?.data?.error || 'Error al registrar el ajuste'
      } finally {
        guardando.value = false
      }
    }

    const resetForm = () => {
      form.producto_id   = ''
      form.cantidad_nueva = null
      form.justificacion = ''
      mensajeExito.value = ''
      mensajeError.value = ''
    }

    return {
      form, errores, guardando, productos, productoSeleccionado,
      diferencia, mensajeExito, mensajeError,
      cargarStockActual, guardar, resetForm,
    }
  }
}
</script>

