<template>
  <div class="login-page">
    <section class="panel-izquierdo">
      <div class="branding-card">

        <div class="cafe-icon-wrap">
          <svg class="cafe-icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">

            <rect x="8" y="24" width="36" height="28" rx="6" fill="none" stroke="white" stroke-width="4"/>

            <path d="M44 30 C56 30, 56 48, 44 48" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/>

            <path d="M18 18 C18 12, 24 12, 24 6" fill="none" stroke="white" stroke-width="3" stroke-linecap="round"/>
            <path d="M28 20 C28 14, 34 14, 34 8" fill="none" stroke="white" stroke-width="3" stroke-linecap="round"/>
            <path d="M22 22 C22 16, 28 16, 28 10" fill="none" stroke="white" stroke-width="3" stroke-linecap="round"/>
          </svg>
        </div>

        <!-- Título + subtítulo -->
        <h1 class="titulo-cafeteria">Café Artesano</h1>
        <p class="subtitulo">Sistema Integral de Gestión para Cafeterías</p>

        <!-- Features -->
        <div class="features">

          <div class="feature-item">
            <div class="feature-icon feature-icon--control">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
              </svg>
            </div>
            <div class="feature-text">
              <h3>Control Total</h3>
              <p>Gestiona inventario, ventas y reportes en tiempo real</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon feature-icon--seguro">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </div>
            <div class="feature-text">
              <h3>Seguro y Confiable</h3>
              <p>Diferentes roles de usuario con permisos específicos</p>
            </div>
          </div>

          <div class="feature-item">
            <div class="feature-icon feature-icon--rapido">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
              </svg>
            </div>
            <div class="feature-text">
              <h3>Rápido y Eficiente</h3>
              <p>Punto de venta optimizado para máxima velocidad</p>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- PANEL DERECHO: Formulario Login -->
    <section class="panel-derecho">
      <div class="login-card">

        <h2 class="login-titulo">Bienvenido de Nuevo</h2>
        <p class="login-subtitulo">Ingresa tus credenciales para continuar</p>

        <!-- Mensaje de error -->
        <div v-if="errorMsg" class="error-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ errorMsg }}</span>
          
        </div>

        <!-- Formulario -->
        <form @submit.prevent="handleLogin" class="login-form">

          <!-- Campo: Nombre de Usuario -->
          <div class="campo">
            <label>Nombre de Usuario</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="8" r="4"/>
              </svg>
              <input
                v-model="username"
                type="text"
                placeholder="Ingresa tu usuario"
                autocomplete="off"
                :disabled="loading"
              />
            </div>
          </div>

          <!-- Campo: Contraseña / PIN -->
          <div class="campo">
            <label>Contraseña / PIN</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <input
                v-model="password"
                :type="mostrarPassword ? 'text' : 'password'"
                placeholder="Contraseña o PIN"
                autocomplete="off"
                :disabled="loading"
              />
              <button type="button" class="toggle-pw" @click="mostrarPassword = !mostrarPassword">
                <svg v-if="!mostrarPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-login" :disabled="loading">
            <span v-if="!loading">Iniciar Sesión &nbsp;→</span>
            <span v-else class="spinner"></span>
          </button>
        </form>

        <!-- Divider -->
        <div class="divider"><span></span></div>

     

        <!-- Ver Menú Público -->
        <a href="#" class="ver-menu" @click.prevent="autoFill">Ver Menú Público &nbsp;→</a>

      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const username = ref('')
const password = ref('')
const errorMsg = ref('')
const loading = ref(false)
const mostrarPassword = ref(false)

const authStore = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
  loading.value = true
  errorMsg.value = ''
    try {
    await authStore.login(username.value, password.value)  // ← Usar el store
    router.push('/dashboard')

  } catch (error) {
    if (error.response?.status === 401) {
      errorMsg.value = 'Credenciales incorrectas.'
    } else {
      errorMsg.value = 'Error al conectar con el servidor.'
    }
  } finally {
    loading.value = false
  }
}


</script>



