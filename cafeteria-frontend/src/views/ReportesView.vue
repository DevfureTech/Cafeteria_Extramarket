<template>
  <div class="reportes-page">
    
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Reportes y Estadísticas</h1>
        <p class="page-subtitle">Analiza el rendimiento del negocio</p>
      </div>
      <div class="page-actions">
        <button class="btn btn--secondary" @click="exportarPDF">
          <FileText :size="18" />
          PDF
        </button>
        <button class="btn btn--success" @click="exportarExcel">
          <FileSpreadsheet :size="18" />
          Excel
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button 
        :class="['tab', { 'tab--active': tabActiva === 'ventas' }]"
        @click="cambiarTab('ventas')"
      >
        Ventas
      </button>
      <button 
        :class="['tab', { 'tab--active': tabActiva === 'productos' }]"
        @click="cambiarTab('productos')"
      >
        Productos
      </button>
      <button 
        :class="['tab', { 'tab--active': tabActiva === 'inventario' }]"
        @click="cambiarTab('inventario')"
      >
        Inventario
      </button>
    </div>

    <!-- Filtros de período -->
    <div class="filtros">
      <button 
        :class="['filtro-btn', { 'filtro-btn--active': periodoActivo === 'hoy' }]"
        @click="cambiarPeriodo('hoy')"
      >
        Hoy
      </button>
      <button 
        :class="['filtro-btn', { 'filtro-btn--active': periodoActivo === 'semana' }]"
        @click="cambiarPeriodo('semana')"
      >
        Última Semana
      </button>
      <button 
        :class="['filtro-btn', { 'filtro-btn--active': periodoActivo === 'mes' }]"
        @click="cambiarPeriodo('mes')"
      >
        Último Mes
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner-large"></div>
      <p>Cargando datos...</p>
    </div>

    <!-- ════════════════════════════════════════════════════════════
         TAB: VENTAS
         ════════════════════════════════════════════════════════════ -->
    <div v-else-if="tabActiva === 'ventas'" class="tab-content">
      
      <!-- KPIs -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon--green">
            <DollarSign :size="24" />
          </div>
          <div>
            <div class="kpi-label">Total Ventas</div>
            <div class="kpi-value">S/. {{ formatNumber(datosVentas.resumen.total_ventas) }}</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon--blue">
            <ShoppingCart :size="24" />
          </div>
          <div>
            <div class="kpi-label">Cantidad de Ventas</div>
            <div class="kpi-value">{{ datosVentas.resumen.cantidad_ventas }}</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon--purple">
            <TrendingUp :size="24" />
          </div>
          <div>
            <div class="kpi-label">Ticket Promedio</div>
            <div class="kpi-value">S/. {{ formatNumber(datosVentas.resumen.ticket_promedio) }}</div>
          </div>
        </div>
      </div>

      <!-- Gráfico: Ventas por Día -->
      <div class="chart-container">
        <h3 class="chart-title">Ventas por Día</h3>
        <canvas ref="chartVentasPorDia"></canvas>
      </div>

      <!-- Gráfico: Distribución por Método de Pago -->
      <div class="chart-container">
        <h3 class="chart-title">Distribución por Método de Pago</h3>
        <canvas ref="chartMetodosPago"></canvas>
      </div>

    </div>

    <!-- ════════════════════════════════════════════════════════════
         TAB: PRODUCTOS
         ════════════════════════════════════════════════════════════ -->
    <div v-else-if="tabActiva === 'productos'" class="tab-content">
      
      <!-- Gráfico: Top 10 Productos Más Vendidos -->
      <div class="chart-container">
        <h3 class="chart-title">Productos Más Vendidos</h3>
        <canvas ref="chartTopProductos"></canvas>
      </div>

      <!-- Tabla: Ranking de Productos -->
      <div class="table-container">
        <h3 class="chart-title">Ranking de Productos</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>PRODUCTO</th>
              <th>CANTIDAD VENDIDA</th>
              <th>TOTAL VENTAS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(producto, index) in datosProductos.ranking" :key="producto.id_producto">
              <td>{{ index + 1 }}</td>
              <td>
                <div class="producto-cell">
                  <div class="producto-nombre">{{ producto.nombre }}</div>
                  <div class="producto-categoria">{{ producto.categoria }}</div>
                </div>
              </td>
              <td>{{ producto.cantidad_vendida }}</td>
              <td>S/. {{ formatNumber(producto.total_ventas) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Productos con Baja Rotación -->
      <div class="alert-section">
        <h3 class="chart-title">Productos con Baja Rotación</h3>
        <div v-if="datosProductos.bajaRotacion.length === 0" class="empty-state-small">
          <p>¡Excelente! Todos los productos tienen buena rotación.</p>
        </div>
        <div v-else class="productos-baja-rotacion">
          <div 
            v-for="producto in datosProductos.bajaRotacion" 
            :key="producto.id_producto"
            class="producto-alerta"
          >
            <div class="producto-info">
              <div class="producto-nombre">{{ producto.nombre }}</div>
              <div class="producto-codigo">{{ producto.codigo }}</div>
            </div>
            <div class="producto-ventas">
              Sin ventas en el período seleccionado
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ════════════════════════════════════════════════════════════
         TAB: INVENTARIO
         ════════════════════════════════════════════════════════════ -->
    <div v-else-if="tabActiva === 'inventario'" class="tab-content">
      
      <!-- KPI: Valor Total del Inventario -->
      <div class="kpi-card-large">
        <div class="kpi-icon-large">
          <Package :size="32" />
        </div>
        <div>
          <div class="kpi-label">Valor Total del Inventario</div>
          <div class="kpi-value-large">S/. {{ formatNumber(datosInventario.resumen.valor_total) }}</div>
          <div class="kpi-meta">{{ datosInventario.resumen.total_productos }} productos registrados</div>
        </div>
      </div>

      <!-- Gráfico: Valor de Inventario por Categoría -->
      <div class="chart-container">
        <h3 class="chart-title">Valor de Inventario por Categoría</h3>
        <canvas ref="chartInventarioCategoria"></canvas>
      </div>

      <!-- Tabla: Stock Disponible -->
      <div class="table-container">
        <h3 class="chart-title">Stock Disponible</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>PRODUCTO</th>
              <th>CATEGORÍA</th>
              <th>STOCK ACTUAL</th>
              <th>PRECIO COMPRA</th>
              <th>VALOR TOTAL</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="item in datosInventario.inventario" 
              :key="item.id_insumo"
              :class="{ 'row-alerta': item.stock_actual <= item.stock_minimo }"
            >
              <td>
                <div class="producto-cell">
                  <div class="producto-nombre">{{ item.nombre }}</div>
                  <div class="producto-codigo">{{ item.codigo }}</div>
                </div>
              </td>
              <td>{{ item.categoria }}</td>
              <td>
                <span :class="['stock-badge', item.stock_actual <= item.stock_minimo ? 'stock-badge--bajo' : '']">
                  {{ item.stock_actual }} {{ item.unidad_medida }}
                </span>
              </td>
              <td>S/. {{ formatNumber(item.precio_compra) }}</td>
              <td class="valor-total">S/. {{ formatNumber(item.valor_total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { 
  DollarSign, 
  ShoppingCart, 
  TrendingUp, 
  FileText, 
  FileSpreadsheet,
  Package
} from 'lucide-vue-next'
import api from '../services/api'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

// ══════════════════════════════════════════════════════════════
// Estado
// ══════════════════════════════════════════════════════════════
const loading = ref(false)
const tabActiva = ref('ventas')
const periodoActivo = ref('hoy')

const datosVentas = ref({
  resumen: {
    total_ventas: 0,
    cantidad_ventas: 0,
    ticket_promedio: 0
  },
  ventas_por_dia: [],
  distribucion_pago: []
})

const datosProductos = ref({
  ranking: [],
  bajaRotacion: []
})

const datosInventario = ref({
  resumen: {
    valor_total: 0,
    total_productos: 0,
    alertas_stock: 0
  },
  inventario: [],
  valor_por_categoria: []
})

// Referencias a canvas
const chartVentasPorDia = ref(null)
const chartMetodosPago = ref(null)
const chartTopProductos = ref(null)
const chartInventarioCategoria = ref(null)

// Instancias de Chart.js
let chartVentasDiaInstance = null
let chartPagoInstance = null
let chartProductosInstance = null
let chartInventarioCatInstance = null

// ══════════════════════════════════════════════════════════════
// Métodos
// ══════════════════════════════════════════════════════════════

function cambiarTab(tab) {
  tabActiva.value = tab
  cargarDatos()
}

function cambiarPeriodo(periodo) {
  periodoActivo.value = periodo
  cargarDatos()
}

async function cargarDatos() {
  loading.value = true
  
  try {
    if (tabActiva.value === 'ventas') {
      await cargarDatosVentas()
    } else if (tabActiva.value === 'productos') {
      await cargarDatosProductos()
    } else if (tabActiva.value === 'inventario') {
      await cargarDatosInventario()
    }
  } catch (error) {
    console.error('Error al cargar datos:', error)
    alert('Error al cargar los datos del reporte')
  } finally {
    loading.value = false
  }
}

async function cargarDatosVentas() {
  const response = await api.get(`/reportes/ventas?periodo=${periodoActivo.value}`)
  datosVentas.value = response.data
  
  await nextTick()
  renderizarGraficosVentas()
}

async function cargarDatosProductos() {
  const [rankingRes, bajaRotacionRes] = await Promise.all([
    api.get(`/reportes/productos/ranking?periodo=${periodoActivo.value}&limite=10`),
    api.get(`/reportes/productos/baja-rotacion?dias=30`)
  ])
  
  datosProductos.value = {
    ranking: rankingRes.data.ranking,
    bajaRotacion: bajaRotacionRes.data.productos
  }
  
  await nextTick()
  renderizarGraficoProductos()
}

async function cargarDatosInventario() {
  const response = await api.get('/reportes/inventario')
  datosInventario.value = response.data
  
  await nextTick()
  renderizarGraficoInventario()
}

function renderizarGraficosVentas() {
  // Destruir gráficos previos
  if (chartVentasDiaInstance) chartVentasDiaInstance.destroy()
  if (chartPagoInstance) chartPagoInstance.destroy()
  
  // Gráfico: Ventas por Día
  const ctx1 = chartVentasPorDia.value?.getContext('2d')
  if (ctx1) {
    chartVentasDiaInstance = new Chart(ctx1, {
      type: 'line',
      data: {
        labels: datosVentas.value.ventas_por_dia.map(v => v.fecha),
        datasets: [{
          label: 'Total (S/.)',
          data: datosVentas.value.ventas_por_dia.map(v => v.total),
          borderColor: '#f97316',
          backgroundColor: 'rgba(249, 115, 22, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true, position: 'bottom' }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    })
  }
  
  // Gráfico: Distribución por Método de Pago
  const ctx2 = chartMetodosPago.value?.getContext('2d')
  if (ctx2) {
    chartPagoInstance = new Chart(ctx2, {
      type: 'doughnut',
      data: {
        labels: datosVentas.value.distribucion_pago.map(p => p.metodo_pago),
        datasets: [{
          data: datosVentas.value.distribucion_pago.map(p => p.total),
          backgroundColor: [
            '#10b981',
            '#3b82f6',
            '#f59e0b',
            '#ef4444'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true, position: 'bottom' }
        }
      }
    })
  }
}

function renderizarGraficoProductos() {
  if (chartProductosInstance) chartProductosInstance.destroy()
  
  const ctx = chartTopProductos.value?.getContext('2d')
  if (ctx) {
    chartProductosInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: datosProductos.value.ranking.map(p => p.nombre),
        datasets: [
          {
            label: 'Cantidad Vendida',
            data: datosProductos.value.ranking.map(p => p.cantidad_vendida),
            backgroundColor: '#3b82f6',
            yAxisID: 'y'
          },
          {
            label: 'Total Ventas (S/.)',
            data: datosProductos.value.ranking.map(p => p.total_ventas),
            backgroundColor: '#10b981',
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        scales: {
          y: {
            type: 'linear',
            position: 'left'
          },
          y1: {
            type: 'linear',
            position: 'right',
            grid: {
              drawOnChartArea: false
            }
          }
        }
      }
    })
  }
}

function renderizarGraficoInventario() {
  if (chartInventarioCatInstance) chartInventarioCatInstance.destroy()
  
  const ctx = chartInventarioCategoria.value?.getContext('2d')
  if (ctx) {
    chartInventarioCatInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: datosInventario.value.valor_por_categoria.map(c => c.categoria),
        datasets: [{
          label: 'Valor (S/.)',
          data: datosInventario.value.valor_por_categoria.map(c => c.valor),
          backgroundColor: '#f97316'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    })
  }
}

function formatNumber(num) {
  return parseFloat(num || 0).toFixed(2)
}

async function exportarPDF() {
  try {
    const response = await api.post('/reportes/exportar/pdf', {
      tipo: tabActiva.value,
      periodo: periodoActivo.value
    }, {
      responseType: 'blob' // Importante para descargar archivos
    })
    
    // Crear URL del blob y descargar
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `reporte_${tabActiva.value}_${Date.now()}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    
  } catch (error) {
    console.error('Error al exportar PDF:', error)
    alert('Error al generar el PDF')
  }
}

async function exportarExcel() {
  try {
    const response = await api.post('/reportes/exportar/excel', {
      tipo: tabActiva.value,
      periodo: periodoActivo.value
    }, {
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `reporte_${tabActiva.value}_${Date.now()}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    
  } catch (error) {
    console.error('Error al exportar Excel:', error)
    alert('Error al generar el Excel')
  }
}

// ══════════════════════════════════════════════════════════════
// Lifecycle
// ══════════════════════════════════════════════════════════════
onMounted(() => {
  cargarDatos()
})

watch(tabActiva, () => {
  cargarDatos()
})

watch(periodoActivo, () => {
  cargarDatos()
})
</script>

<style scoped>

.reportes-page {
  animation: fadeUp 0.35s ease both;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 32px;
  flex-wrap: wrap;
  gap: 16px;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--slate-700);
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 0.94rem;
  color: var(--slate-500);
}

.page-actions {
  display: flex;
  gap: 12px;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  border-bottom: 2px solid var(--slate-200);
}

.tab {
  padding: 12px 24px;
  background: transparent;
  border: none;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--slate-600);
  cursor: pointer;
  transition: all 0.2s;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
}

.tab:hover {
  color: orange;
}

.tab--active {
  color: orange;
  border-bottom-color: orange;
}

/* Filtros */
.filtros {
  display: flex;
  gap: 12px;
  margin-bottom: 32px;
}

.filtro-btn {
  padding: 10px 20px;
  border-radius: 12px;
  border: 1.5px solid var(--slate-300);
  background: rgb(237, 164, 6);
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--slate-600);
  cursor: pointer;
  transition: all 0.2s;
}

.filtro-btn:hover {
  border-color: orange;
  color: orange;
}

.filtro-btn--active {
  background: orange;
  color: rgba(19, 5, 5, 0.938);
  border-color: orange;
}

/* KPIs */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.kpi-card {
  background: rgb(236, 123, 18);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.kpi-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.kpi-icon--green {
  background: green;
  color: rgb(23, 227, 23);
}

.kpi-icon--blue {
  background: blue;
  color: rgb(10, 24, 64);
}

.kpi-icon--purple {
  background: rgb(196, 22, 196);
  color: rgb(101, 1, 101);
}

.kpi-label {
  font-size: 0.85rem;
  color: var(--slate-500);
  margin-bottom: 4px;
}

.kpi-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--slate-700);
}

.kpi-card-large {
  background: white;
  border-radius: 16px;
  padding: 32px;
  display: flex;
  align-items: center;
  gap: 24px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  margin-bottom: 32px;
}

.kpi-icon-large {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  background: var(--orange-50, #fff7ed);
  color: var(--orange-primary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.kpi-value-large {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--slate-700);
}

.kpi-meta {
  font-size: 0.9rem;
  color: var(--slate-500);
  margin-top: 4px;
}

/* Charts */
.chart-container {
  background: white;
  border-radius: 16px;
  padding: 28px;
  margin-bottom: 32px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.chart-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--slate-700);
  margin-bottom: 20px;
}

canvas {
  max-height: 400px;
}

/* Tablas */
.table-container {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  margin-bottom: 32px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: var(--slate-50);
}

.data-table th {
  text-align: left;
  padding: 16px 20px;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: var(--slate-500);
}

.data-table tbody tr {
  border-bottom: 1px solid var(--slate-200);
  transition: background 0.2s;
}

.data-table tbody tr:hover {
  background: var(--slate-50);
}

.data-table td {
  padding: 18px 20px;
  font-size: 0.9rem;
}

.producto-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.producto-nombre {
  font-weight: 600;
  color: var(--slate-700);
}

.producto-categoria,
.producto-codigo {
  font-size: 0.82rem;
  color: var(--slate-500);
}

.stock-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  background: var(--green-100);
  color: var(--green-700);
}

.stock-badge--bajo {
  background: var(--red-100);
  color: var(--red-700);
}

.row-alerta {
  background: var(--red-100, #fef2f2) !important;
}

.valor-total {
  font-weight: 600;
  color: var(--slate-700);
}

/* Alertas */
.alert-section {
  background: white;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.productos-baja-rotacion {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.producto-alerta {
  padding: 16px;
  border-radius: 12px;
  background: var(--red-50, #fef2f2);
  border: 1.5px solid var(--red-200, #fecaca);
}

.producto-info {
  margin-bottom: 8px;
}

.producto-ventas {
  font-size: 0.82rem;
  color: var(--red-700);
  font-style: italic;
}

/* Estados */
.loading-state,
.empty-state-small {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  color: var(--slate-500);
}

.spinner-large {
  width: 48px;
  height: 48px;
  border: 4px solid var(--slate-200);
  border-top-color: var(--orange-primary);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  margin-bottom: 16px;
}

/* Botones */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.94rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn--secondary {
  background: var(--red-600, #dc2626);
  color: white;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
}

.btn--secondary:hover {
  background: var(--red-700, #b91c1c);
}

.btn--success {
  background: var(--green-600, #16a34a);
  color: white;
  box-shadow: 0 2px 8px rgba(22, 163, 74, 0.25);
}

.btn--success:hover {
  background: var(--green-700, #15803d);
}

/* Animaciones */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0);    }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .page-actions {
    width: 100%;
    flex-direction: column;
  }
  
  .tabs {
    overflow-x: auto;
  }
  
  .filtros {
    overflow-x: auto;
  }
}
</style>