import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './assets/main.css'
import './assets/style.css'

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)
app.use(router)

// Initialize auth state from localStorage
import { useAuthStore } from './stores/auth'
const authStore = useAuthStore(pinia)
authStore.checkAuth()

app.mount('#app')
