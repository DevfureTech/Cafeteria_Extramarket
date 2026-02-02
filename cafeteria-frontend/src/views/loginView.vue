<template>
  <div
    class="min-h-screen bg-gradient-to-br from-[#f5f1ed] via-white to-[#f5f1ed] flex items-center justify-center p-4"
  >
    <div class="w-full max-w-5xl grid md:grid-cols-2 gap-8">
      
      <!-- LADO IZQUIERDO -->
      <div
        class="hidden md:flex flex-col justify-center bg-gradient-to-br from-[#6f4e37] to-[#8b7355] text-white rounded-3xl p-10 shadow-xl"
      >
        <h1 class="text-4xl font-bold mb-4">Café Artesano</h1>
        <p class="text-lg opacity-90 mb-10">
          Sistema Integral de Gestión para Cafeterías
        </p>

        <ul class="space-y-4 text-sm">
          <li class="flex items-start gap-3">
            <span class="text-xl">📊</span>
            <span>Control de ventas e inventario</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="text-xl">🔒</span>
            <span>Acceso seguro por roles</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="text-xl">⚡</span>
            <span>Rápido y fácil de usar</span>
          </li>
        </ul>
      </div>

      <!-- FORMULARIO -->
      <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
        <h2 class="text-3xl font-bold text-[#6f4e37] mb-2 text-center">
          Iniciar Sesión
        </h2>
        <p class="text-center text-[#8b7355] mb-8">
          Ingresa tus credenciales
        </p>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- USUARIO -->
          <div>
            <label class="block text-sm font-semibold text-[#6f4e37] mb-2">
              Usuario
            </label>
            <input
              v-model="username"
              type="text"
              class="w-full p-4 rounded-2xl bg-[#f5f1ed] focus:ring-2 focus:ring-[#6f4e37] outline-none"
              placeholder="Usuario"
            />
          </div>

          <!-- PASSWORD / PIN -->
          <div>
            <label class="block text-sm font-semibold text-[#6f4e37] mb-2">
              Contraseña / PIN
            </label>
            <input
              v-model="password"
              type="password"
              class="w-full p-4 rounded-2xl bg-[#f5f1ed] focus:ring-2 focus:ring-[#6f4e37] outline-none"
              placeholder="Contraseña o PIN"
            />
          </div>

          <!-- ERROR -->
          <div
            v-if="error"
            class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-xl text-sm"
          >
            {{ error }}
          </div>

          <!-- BOTÓN -->
          <button
            type="submit"
            class="w-full bg-gradient-to-r from-[#6f4e37] to-[#8b7355] text-white py-4 rounded-2xl font-semibold hover:opacity-90 transition"
          >
            Iniciar Sesión
          </button>
        </form>

        <!-- CREDENCIALES -->
        <div class="mt-8 text-center text-sm text-[#6f4e37]">
          <p class="font-semibold">Credenciales de prueba</p>
          <p>Usuario: <b>admin</b> | Password: <b>admin123</b></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const username = ref('')
const password = ref('')
const error = ref('')

const authStore = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
  try {
    await authStore.login({
      nombre_usuario: username.value,
      contraseña_administrador: password.value
    })
    router.push('/dashboard')
  } catch (error) {
    console.error(error.response?.data)
    alert('Credenciales incorrectas')
  }
}


</script>

