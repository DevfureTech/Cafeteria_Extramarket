 <template>
  <div class="users-page">

    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Gestión de Usuarios</h1>
        <p class="page-subtitle">Administra usuarios y roles del sistema</p>
      </div>
      <div class="page-actions">
        
        <button class="btn btn--primary" @click="openCreateModal">
          <Plus :size="18" />
          Nuevo Usuario
        </button>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="loading-state">
      <div class="spinner-large"></div>
      <p>Cargando usuarios...</p>
    </div>

    <!-- Tabla de usuarios -->
    <div v-else-if="usuarios.length > 0" class="table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th>USUARIO</th>
            <th>ROL</th>
            <th>ESTADO</th>
            <th>ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in usuarios" :key="user.id_usuario" class="user-row">
            <!-- Usuario -->
            <td>
              <div class="user-cell">
                <div class="user-avatar" :class="'user-avatar--' + getRolClass(user.rol?.nombre)">
                  <Shield :size="20" />
                </div>
                <div>
                  <div class="user-name">{{ user.nombre_completo }}</div>
                  <div class="user-username">@{{ user.nombre_usuario }}</div>
                </div>
              </div>
            </td>

            <!-- Rol -->
            <td>
              <span class="badge badge--rol" :class="'badge--' + getRolClass(user.rol?.nombre)">
                {{ user.rol?.nombre || 'Sin rol' }}
              </span>
            </td>

            <!-- Estado -->
            <td>
              <span class="badge badge--estado" :class="user.activo ? 'badge--activo' : 'badge--inactivo'">
                {{ user.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </td>

            <!-- Acciones -->
            <td>
              <div class="actions">
                <button class="icon-btn icon-btn--edit" @click="openEditModal(user)" title="Editar">
                  <Pencil :size="16" />
                </button>
                <button class="icon-btn icon-btn--delete" @click="confirmDelete(user)" title="Eliminar">
                  <Trash2 :size="16" />
                </button>
                  <!-- AUDITORIA (NUEVO) -->
              <button
                class="icon-btn"
                @click="loadAudit(user.id_usuario)"
                title="Ver auditoría"
              >
                <Shield :size="16" />
              </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Estado vacío -->
    <div v-else class="empty-state">
      <Users :size="64" color="#9ca3af" />
      <p>No hay usuarios registrados</p>
      <button class="btn btn--primary" @click="openCreateModal">
        <Plus :size="18" />
        Crear primer usuario
      </button>
    </div>

    <!-- ════════════════════════════════════════════════════════════
         MODAL: Crear/Editar Usuario
         ════════════════════════════════════════════════════════════ -->
    <transition name="modal">
      <div v-if="showUserModal" class="modal-overlay" @click="closeUserModal">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>{{ editingUser ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
            <button class="modal-close" @click="closeUserModal">
              <X :size="20" />
            </button>
          </div>

          <form @submit.prevent="saveUser" class="modal-body">

            <!-- Nombre Completo -->
            <div class="form-group">
              <label>Nombre Completo</label>
              <input
                v-model="formData.nombre_completo"
                type="text"
                placeholder="Ej: Juan Pérez"
                :class="{ 'input-error': errors.nombre_completo }"
              />
              <span v-if="errors.nombre_completo" class="error-text">{{ errors.nombre_completo[0] }}</span>
            </div>

            <!-- Nombre de Usuario -->
            <div class="form-group">
              <label>Nombre de Usuario</label>
              <input
                v-model="formData.nombre_usuario"
                type="text"
                placeholder="Ej: jperez"
                :class="{ 'input-error': errors.nombre_usuario }"
              />
              <span v-if="errors.nombre_usuario" class="error-text">{{ errors.nombre_usuario[0] }}</span>
            </div>

            <!-- Rol -->
            <div class="form-group">
              <label>Rol</label>
              <select v-model="formData.id_rol" :class="{ 'input-error': errors.id_rol }">
                <option value="">Seleccionar rol</option>
                <option v-for="rol in roles" :key="rol.id" :value="rol.id">
                  {{ rol.nombre }}
                </option>
              </select>
              <span v-if="errors.id_rol" class="error-text">{{ errors.id_rol[0] }}</span>
            </div>

            <!-- Password (solo si rol === Administrador) -->
            <div v-if="isAdminRole" class="form-group">
              <label>Contraseña {{ editingUser ? '(dejar vacío para no cambiar)' : '' }}</label>
              <input
                v-model="formData.password"
                type="password"
                placeholder="Mínimo 8 caracteres"
                :class="{ 'input-error': errors.password }"
              />
              <span v-if="errors.password" class="error-text">{{ errors.password[0] }}</span>
            </div>

            <!-- PIN (si rol !== Administrador) -->
            <div v-if="!isAdminRole && formData.id_rol" class="form-group">
              <label>PIN (4 dígitos){{ editingUser ? ' - dejar vacío para no cambiar' : '' }}</label>
              <input
                v-model="formData.pin_usuario"
                type="text"
                maxlength="4"
                placeholder="1234"
                :class="{ 'input-error': errors.pin_usuario }"
              />
              <span v-if="errors.pin_usuario" class="error-text">{{ errors.pin_usuario[0] }}</span>
            </div>

            <!-- Estado -->
            <div class="form-group">
              <label>Estado</label>
              <select v-model="formData.activo">
                <option :value="true">Activo</option>
                <option :value="false">Inactivo</option>
              </select>
            </div>

            <!-- Botones -->
            <div class="modal-footer">
              <button type="button" class="btn btn--ghost" @click="closeUserModal">
                Cancelar
              </button>
              <button type="submit" class="btn btn--primary" :disabled="saving">
                <span v-if="!saving">{{ editingUser ? 'Actualizar' : 'Crear' }}</span>
                <span v-else class="spinner"></span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- ════════════════════════════════════════════════════════════
         MODAL: Log de Auditoría
         ════════════════════════════════════════════════════════════ -->
    <transition name="modal">
      <div v-if="showAuditModal" class="modal-overlay" @click="showAuditModal = false">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>Log de Auditoría</h2>
            <button class="modal-close" @click="showAuditModal = false">
              <X :size="20" />
            </button>
          </div>

          <div class="modal-body">

  <!-- Loading -->
  <div v-if="loadingAudit" class="audit-info">
    <p>Cargando auditoría...</p>
  </div>

  <!-- Sin datos -->
  <div v-else-if="auditLogs.length === 0" class="audit-info">
    <p>No hay registros de auditoría</p>
  </div>

  <!-- Logs reales -->
  <div v-else>
    <div
      v-for="log in auditLogs"
      :key="log.id"
      class="audit-entry"
    >
      <div class="audit-entry__header">
        <span class="audit-entry__action">
          {{ log.operacion }}
        </span>

        <span class="audit-entry__date">
          {{ new Date(log.fecha).toLocaleString() }}
        </span>
      </div>

      <div class="audit-entry__details">
        Realizado por:
        {{ log.usuario?.nombre_completo || 'Sistema' }}
      </div>

      <div class="audit-entry__meta">
        IP: {{ log.ip }}
      </div>
    </div>
  </div>

  <button class="btn btn--secondary btn--block" @click="showAuditModal = false">
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
import { Users, Plus, Shield, Pencil, Trash2, X } from 'lucide-vue-next'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

/* --- Reemplazo: usar getAuthHeaders en lugar de setAuthHeader --- */
const router = useRouter()
const auth = useAuthStore()

function getAuthHeaders() {
  const token = auth?.token || localStorage.getItem('token')
  if (!token) return null
  return { Authorization: `Bearer ${token}` }
}

// ══════════════════════════════════════════════════════════════
// Estado
// ══════════════════════════════════════════════════════════════
const loading = ref(false)
const saving = ref(false)
const usuarios = ref([])
const roles = ref([
  { id: 1, nombre: 'Administrador' },
  { id: 2, nombre: 'Supervisor' },
  { id: 3, nombre: 'Empleado' },
])

// Modales
const showUserModal = ref(false)
const showAuditModal = ref(false)
const editingUser = ref(null)
const auditLogs = ref([])
const loadingAudit = ref(false)
const auditUserId = ref(null)



// Formulario
const formData = ref({
  nombre_completo: '',
  nombre_usuario: '',
  id_rol: '',
  password: '',
  pin: '',
  activo: true,
})
const errors = ref({})

// ══════════════════════════════════════════════════════════════
// Computed
// ══════════════════════════════════════════════════════════════
const isAdminRole = computed(() => {
  return formData.value.id_rol === 1
})

// ══════════════════════════════════════════════════════════════
// Métodos (cambios mínimos: asegurar header antes de llamar)
// ══════════════════════════════════════════════════════════════

/**
 * Cargar usuarios desde la API
 */
async function loadUsers() {
  loading.value = true
  try {
    const headers = getAuthHeaders()
    if (!headers) {
      router.push('/login')
      return
    }
    const response = await api.get('/usuarios', { headers })
    usuarios.value = response.data.usuarios
  } catch (error) {
    console.error('Error al cargar usuarios:', error)
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.push('/login')
      return
    }
    alert('Error al cargar usuarios')
  } finally {
    loading.value = false
  }
}

/**
 * Abrir modal de crear
 */
function openCreateModal() {
  editingUser.value = null
  formData.value = {
    nombre_completo: '',
    nombre_usuario: '',
    id_rol: '',
    password: '',
    pin_usuario: '',
    activo: true,
  }
  errors.value = {}
  showUserModal.value = true
}

/**
 * Abrir modal de editar
 */
function openEditModal(user) {
  editingUser.value = user
  formData.value = {
    nombre_completo: user.nombre_completo,
    nombre_usuario: user.nombre_usuario,
    id_rol: user.id_rol,
    password: '',
    pin_usuario: '',
    activo: user.activo,
  }
  errors.value = {}
  showUserModal.value = true
}

/**
 * Cerrar modal de usuario
 */
function closeUserModal() {
  showUserModal.value = false
  editingUser.value = null
  formData.value = {
    nombre_completo: '',
    nombre_usuario: '',
    id_rol: '',
    password: '',
    pin_usuario: '',
    activo: true,
  }
  errors.value = {}
}

/**
 * Guardar usuario (crear o actualizar)
 */
async function saveUser() {
  saving.value = true
  errors.value = {}

  try {
    const headers = getAuthHeaders()
    if (!headers) {
      router.push('/login')
      return
    }

    const data = {
      nombre_completo: formData.value.nombre_completo,
      nombre_usuario: formData.value.nombre_usuario,
      id_rol: formData.value.id_rol,
      activo: formData.value.activo,
    }

    // ✅ Cambiar a contraseña_administrador
    if (isAdminRole.value && formData.value.password) {
      data.contraseña_administrador = formData.value.password  // ← Cambio aquí
    }

    if (!isAdminRole.value && formData.value.pin_usuario && formData.value.pin_usuario.trim() !== '') {
      data.pin_usuario = formData.value.pin_usuario
    }

    if (editingUser.value) {
      await api.put(`/usuarios/${editingUser.value.id_usuario}`, data, { headers })
      alert('Usuario actualizado exitosamente')
    } else {
      await api.post('/usuarios', data, { headers })
      alert('Usuario creado exitosamente')
    }

    closeUserModal()
    loadUsers()

  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.push('/login')
    } else {
      console.error('Error guardar usuario:', error)
      alert('Error al guardar usuario')
    }
  } finally {
    saving.value = false
  }
}

/**
 * Confirmar eliminación
 */
async function confirmDelete(user) {
  if (!confirm(`¿Deseas desactivar al usuario "${user.nombre_completo}"?`)) {
    return
  }

  try {
    const headers = getAuthHeaders()
    if (!headers) {
      router.push('/login')
      return
    }
    // Usar id_usuario
    await api.delete(`/usuarios/${user.id_usuario}`, { headers })
    alert('Usuario desactivado exitosamente')
    loadUsers()
  } catch (error) {
    console.error('Error al desactivar usuario:', error)
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.push('/login')
      return
    }
    alert('Error al desactivar usuario')
  }
}



async function loadAudit(userId) {
  console.log('🔥 loadAudit ejecutado', userId)

  auditUserId.value = userId
  showAuditModal.value = true
  loadingAudit.value = true
  auditLogs.value = []
 
  try {
    const headers = getAuthHeaders()
    if (!headers) return

    const response = await api.get(`/usuarios/auditoria/${userId}`, { headers })

    console.log('🔥 RESPUESTA BACKEND:', response.data)

    auditLogs.value = response.data.logs || []

  } catch (error) {
    console.error('❌ Error auditoría:', error)
  } finally {
    loadingAudit.value = false
  }
}



/**
 * Helper: Clase CSS según rol
 */
function getRolClass(rolNombre) {
  const map = {
    'Administrador': 'admin',
    'Supervisor': 'supervisor',
    'Empleado': 'empleado',
  }
  return map[rolNombre] || 'default'
}

// ══════════════════════════════════════════════════════════════
// Lifecycle
// ══════════════════════════════════════════════════════════════
onMounted(() => {
  if (!auth.isAuthenticated && !localStorage.getItem('token')) {
    router.push('/login')
    return
  }
  loadUsers()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════════════
   Variables
   ══════════════════════════════════════════════════════════════ */
:root {
  --orange-primary:  #f97316;
  --orange-dark:     #ea580c;
  --slate-700:       #334155;
  --slate-600:       #475569;
  --slate-500:       #64748b;
  --slate-400:       #94a3b8;
  --slate-300:       #cbd5e1;
  --slate-200:       #e2e8f0;
  --slate-100:       #f1f5f9;
  --slate-50:        #f8fafc;
  --green-100:       #dcfce7;
  --green-700:       #15803d;
  --red-100:         #fee2e2;
  --red-700:         #b91c1c;
  --purple-100:      #f3e8ff;
  --purple-700:      #7e22ce;
  --blue-100:        #dbeafe;
  --blue-700:        #1d4ed8;
  --yellow-50:       #fef9e7;
  --yellow-600:      #ca8a04;
}

/* ══════════════════════════════════════════════════════════════
   Layout
   ══════════════════════════════════════════════════════════════ */
.users-page {
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

/* ══════════════════════════════════════════════════════════════
   Botones
   ══════════════════════════════════════════════════════════════ */
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
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn--primary {
  background: var(--orange-primary);
  color: #0a0505;
  box-shadow: 0 2px 8px rgba(249,115,22,0.25);
}
.btn--primary:hover:not(:disabled) {
  background: var(--orange-dark);
  box-shadow: 0 4px 14px rgba(249,115,22,0.35);
}

.btn--secondary {
  background: var(--slate-600);
  color: #0a0808;
  box-shadow: 0 2px 8px rgba(71,85,105,0.25);
}
.btn--secondary:hover {
  background: var(--slate-700);
}

.btn--ghost {
  background: transparent;
  color: var(--slate-600);
  border: 1.5px solid var(--slate-300);
}
.btn--ghost:hover {
  background: var(--slate-50);
}

.btn--block {
  width: 100%;
  justify-content: center;
}

/* Iconos */
.icon-btn {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  background: transparent;
}
.icon-btn--edit {
  color: var(--slate-600);
}
.icon-btn--edit:hover {
  background: var(--orange-primary);
  color: #fff;
}
.icon-btn--delete {
  color: var(--slate-600);
}
.icon-btn--delete:hover {
  background: var(--red-700);
  color: #fff;
}

/* ══════════════════════════════════════════════════════════════
   Tabla
   ══════════════════════════════════════════════════════════════ */
.table-container {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}
.users-table thead {
  background: var(--slate-50);
}
.users-table th {
  text-align: left;
  padding: 16px 20px;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: var(--slate-500);
}
.users-table tbody tr {
  border-bottom: 1px solid var(--slate-200);
  transition: background 0.2s;
}
.users-table tbody tr:hover {
  background: var(--slate-50);
}
.users-table td {
  padding: 18px 20px;
}

/* User cell */
.user-cell {
  display: flex;
  align-items: center;
  gap: 14px;
}
.user-avatar {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.user-avatar--admin {
  background: var(--yellow-50);
  color: var(--yellow-600);
}
.user-avatar--supervisor {
  background: var(--blue-100);
  color: var(--blue-700);
}
.user-avatar--empleado {
  background: var(--slate-100);
  color: var(--slate-500);
}
.user-name {
  font-weight: 600;
  color: var(--slate-700);
  font-size: 0.94rem;
}
.user-username {
  font-size: 0.84rem;
  color: var(--slate-500);
}

/* Badges */
.badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 0.84rem;
  font-weight: 600;
}
.badge--admin {
  background: var(--purple-100);
  color: var(--purple-700);
}
.badge--supervisor {
  background: var(--blue-100);
  color: var(--blue-700);
}
.badge--empleado {
  background: var(--slate-100);
  color: var(--slate-600);
}
.badge--activo {
  background: var(--green-100);
  color: var(--green-700);
}
.badge--inactivo {
  background: var(--red-100);
  color: var(--red-700);
}

/* Actions */
.actions {
  display: flex;
  gap: 6px;
}

/* ══════════════════════════════════════════════════════════════
   Estados
   ══════════════════════════════════════════════════════════════ */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  text-align: center;
  color: var(--slate-500);
}
.empty-state p {
  margin: 16px 0 24px;
  font-size: 1.05rem;
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

/* ══════════════════════════════════════════════════════════════
   Modal
   ══════════════════════════════════════════════════════════════ */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 999;
  background: rgba(0,0,0,0.55);
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
  box-shadow: 0 20px 60px rgba(0,0,0,0.35);
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 28px;
  border-bottom: 1px solid var(--slate-200);
}
.modal-header h2 {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--slate-700);
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
  color: var(--slate-600);
}
.modal-close:hover {
  background: var(--slate-100);
}

.modal-body {
  padding: 28px;
}
.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid var(--slate-200);
}

/* Form */
.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--slate-700);
  margin-bottom: 6px;
}
.form-group input,
.form-group select {
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid var(--slate-300);
  border-radius: 12px;
  font-size: 0.94rem;
  color: var(--slate-700);
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--orange-primary);
  box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
}
.input-error {
  border-color: var(--red-700) !important;
}
.error-text {
  display: block;
  margin-top: 6px;
  font-size: 0.82rem;
  color: var(--red-700);
}

/* Spinner pequeño */
.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 3px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* Auditoría */
.audit-info {
  background: var(--slate-50);
  border: 1.5px solid var(--slate-200);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 20px;
  text-align: center;
  color: var(--slate-600);
  font-size: 0.92rem;
}
.audit-entry {
  background: var(--slate-50);
  border-radius: 12px;
  padding: 18px;
  margin-bottom: 20px;
}
.audit-entry__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}
.audit-entry__action {
  font-weight: 600;
  color: var(--slate-700);
  font-size: 0.95rem;
}
.audit-entry__date {
  font-size: 0.82rem;
  color: var(--slate-500);
}
.audit-entry__details {
  font-size: 0.9rem;
  color: var(--slate-600);
  margin-bottom: 6px;
}
.audit-entry__meta {
  font-size: 0.84rem;
  color: var(--slate-500);
}

/* ══════════════════════════════════════════════════════════════
   Animaciones
   ══════════════════════════════════════════════════════════════ */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0);    }
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Modal transitions */
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
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .page-actions {
    width: 100%;
    flex-direction: column;
  }
  .users-table {
    font-size: 0.88rem;
  }
  .users-table th,
  .users-table td {
    padding: 12px 14px;
  }
}
</style>

