<template>
  <div class="landing-page">
    <!-- ════════════════════════════════════════════════════════
         HERO SECTION - Inspirado en la primera imagen
         ════════════════════════════════════════════════════════ -->
    <section class="hero">
      <div class="hero__grain"></div>
      <div class="hero__container container">
        <div class="hero__brand fade-in-down">
          <div class="hero__logo">
            <Coffee :size="60" />
          </div>
          <h1 class="hero__title">Descubre el Arte del Café</h1>
          <p class="hero__subtitle">Premium Coffee & Pastries</p>
        </div>
        
        <div class="hero__cta fade-in-up" style="animation-delay: 0.2s">
          <p class="hero__tagline">
            Café de especialidad tostado artesanalmente, alimentos frescos y postres elaborados con pasión cada día
          </p>
          <div class="hero__buttons">
            <button class="btn btn--primary btn--large" @click="scrollToMenu">
              Ver Menú Completo 
            </button>
            <router-link to="/login" class="btn btn--primary btn--large">
                Iniciar sesión
            </router-link>
            <button class="btn btn--secondary btn--large">
              Contáctanos
            </button>
          </div>
        </div>

        <!-- Stats de la primera imagen -->
        <div class="hero__stats">
          <div class="stat-item">
            <span class="stat-value">12+</span>
            <span class="stat-label">Años de Experiencia</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">50+</span>
            <span class="stat-label">Productos Artesanales</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">10K+</span>
            <span class="stat-label">Clientes Felices</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">5★</span>
            <span class="stat-label">Calificación Promedio</span>
          </div>
        </div>
      </div>
      
      <div class="hero__decoration">
        <div class="bean bean--1"></div>
        <div class="bean bean--2"></div>
        <div class="bean bean--3"></div>
      </div>
    </section>

    <!-- ════════════════════════════════════════════════════════
         MENÚ COMPLETO - Inspirado en la segunda imagen
         ════════════════════════════════════════════════════════ -->
    <section ref="menuSection" class="menu">
      <div class="container">
        <div class="menu__header">
          <h2 class="section-title fade-in-up">Nuestro Menú</h2>
          <p class="section-subtitle fade-in-up" style="animation-delay: 0.1s">
            Explora nuestra selección cuidadosamente curada de cafés, alimentos y postres
          </p>
        </div>

        <!-- Categorías principales - Estilo menú navegación -->
        <div class="menu__categories-nav fade-in-up" style="animation-delay: 0.2s">
          <button
            v-for="categoria in categoriasPrincipales"
            :key="categoria.id"
            class="category-nav-btn"
            :class="{ active: categoriaActiva === categoria.id }"
            @click="cambiarCategoria(categoria.id)"
          >
            {{ categoria.nombre }}
          </button>
        </div>

        <!-- Filtros de subcategorías -->
        <div class="menu__subcategories fade-in-up" style="animation-delay: 0.25s">
          <button
            v-for="subcategoria in subcategoriasFiltradas"
            :key="subcategoria.categoria_id"
            class="subcategory-btn"
            :class="{ active: subcategoriaSeleccionada === subcategoria.categoria_id }"
            @click="filtrarPorSubcategoria(subcategoria.categoria_id)"
          >
            {{ subcategoria.nombre }}
          </button>
          <button
            class="subcategory-btn"
            :class="{ active: subcategoriaSeleccionada === 'todos' }"
            @click="filtrarPorSubcategoria('todos')"
          >
            Todo el Menú
          </button>
        </div>

        <!-- Grid de productos del menú -->
        <div v-if="cargandoMenu" class="menu__loading">
          <div class="spinner"></div>
          <p>Cargando nuestro menú...</p>
        </div>

        <div v-else-if="productosMenu.length === 0" class="menu__empty">
          <Coffee :size="48" />
          <p>Próximamente más opciones en nuestro menú</p>
        </div>

        <div v-else class="menu-grid">
          <div
            v-for="(item, index) in productosMenu"
            :key="item.producto_id"
            class="menu-item fade-in-up"
            :style="{ animationDelay: `${0.3 + index * 0.05}s` }"
          >
            <div class="menu-item__image">
              <Coffee :size="32" />
            </div>
            
            <div class="menu-item__content">
              <div class="menu-item__header">
                <h3 class="menu-item__name">{{ item.nombre }}</h3>
                <span class="menu-item__price">{{ formatCurrency(item.precio_venta || item.precio_compra * 1.5) }}</span>
              </div>
              
              <p class="menu-item__description">{{ item.descripcion || getDefaultDescription(item.nombre) }}</p>
              
              <div class="menu-item__rating">
                <span class="stars">⭐️⭐️⭐️⭐️</span>
                <span class="rating-value">(4.9)</span>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ════════════════════════════════════════════════════════
         UBICACIÓN Y CONTACTO - Inspirado en la tercera imagen
         ════════════════════════════════════════════════════════ -->
    <section class="location">
      <div class="container">
        <div class="location__content">
          <div class="location__info fade-in-up">
            <h2 class="location__title">¿Listo para disfrutar?</h2>
            <p class="location__subtitle">
              Visítanos hoy y descubre por qué somos el café favorito de la ciudad
            </p>
            
            <div class="location__actions">
              <button class="btn btn--primary">
                <MapPin :size="20" />
                Ver Ubicación
              </button>
              <button class="btn btn--secondary">
                <Phone :size="20" />
                Llamar Ahora
              </button>
            </div>

            <div class="location__details">
              <div class="detail-block">
                <h4><Clock :size="18" /> Horarios</h4>
                <p>Lunes - Viernes: 7:00 AM - 8:00 PM</p>
                <p>Sábados: 8:00 AM - 9:00 PM</p>
                <p>Domingos: 9:00 AM - 6:00 PM</p>
              </div>

              <div class="detail-block">
                <h4><Phone :size="18" /> Contacto</h4>
                <p>+51 999 999 999</p>
                <p class="small">Lun - Dom: 7AM - 9PM</p>
                <p>hola@cafeartesano.com</p>
                <p class="small">Respuesta en 24hrs</p>
              </div>

              <div class="detail-block">
                <h4><MapPin :size="18" /> Ubicación</h4>
                <p>Calle Principal #123</p>
                <p>Colonia Centro</p>
                <p>Trujillo , 01000</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ════════════════════════════════════════════════════════
         FOOTER - Inspirado en la tercera imagen
         ════════════════════════════════════════════════════════ -->
    <footer class="footer">
      <div class="container">
        <div class="footer__content">
          <div class="footer__brand">
            <Coffee :size="32" />
            <div>
              <span class="brand-name">Café Artesano</span>
              <span class="brand-tagline">Premium Coffee</span>
            </div>
          </div>
          
          <div class="footer__info">
            <p class="footer__description">
              Café de especialidad tostado artesanalmente y alimentos frescos preparados con amor desde 2013.
            </p>
          </div>

          <div class="footer__links">
            <a href="#">Política de Privacidad</a>
            <span class="separator">|</span>
            <a href="#">Términos de Servicio</a>
          </div>

          <p class="footer__copy">
            © {{ new Date().getFullYear() }} Café Artesano. Todos los derechos reservados.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  Coffee, MapPin, Phone, Clock, 
  AlertCircle, DollarSign, Truck 
} from 'lucide-vue-next'
import axios from 'axios'

// API pública
const apiPublica = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// State
const menuSection = ref(null)
const productos = ref([])
const categorias = ref([])
const categoriaActiva = ref('bebidas')
const subcategoriaSeleccionada = ref('todos')
const cargandoMenu = ref(true)

// Categorías principales para navegación del menú
const categoriasPrincipales = [
  { id: 'bebidas', nombre: 'Bebidas Calientes', icon: '☕' },
  { id: 'bebidas-frias', nombre: 'Bebidas Frías', icon: '🥤' },
  { id: 'alimentos', nombre: 'Alimentos', icon: '🥐' },
  { id: 'postres', nombre: 'Postres', icon: '🍰' }
]

// Mapeo de categorías de la BD a categorías del menú
const mapCategoriaToMenu = (categoriaNombre) => {
  const nombre = categoriaNombre.toLowerCase()
  if (nombre.includes('café') || nombre.includes('caliente') || nombre.includes('bebida caliente')) 
    return 'bebidas'
  if (nombre.includes('fría') || nombre.includes('bebida fría') || nombre.includes('frappé')) 
    return 'bebidas-frias'
  if (nombre.includes('comida') || nombre.includes('alimento') || nombre.includes('sándwich')) 
    return 'alimentos'
  if (nombre.includes('postre') || nombre.includes('pastel') || nombre.includes('dulce')) 
    return 'postres'
  return 'bebidas'
}

// Productos filtrados para el menú
const productosMenu = computed(() => {
  let filtrados = productos.value.filter(p => {
    const categoriaMenu = mapCategoriaToMenu(p.categoria_nombre || '')
    return categoriaMenu === categoriaActiva.value
  })

  if (subcategoriaSeleccionada.value !== 'todos') {
    filtrados = filtrados.filter(p => 
      p.categoria_id === subcategoriaSeleccionada.value
    )
  }

  return filtrados.slice(0, 6) // Mostrar 6 items por categoría
})

// Subcategorías disponibles para la categoría activa
const subcategoriasFiltradas = computed(() => {
  return categorias.value.filter(c => {
    const categoriaMenu = mapCategoriaToMenu(c.nombre)
    return categoriaMenu === categoriaActiva.value
  })
})

// Métodos
const cargarProductos = async () => {
  try {
    cargandoMenu.value = true
    const response = await apiPublica.get('/productos/publicos')
    productos.value = response.data
  } catch (error) {
    console.error('Error cargando productos:', error)
    productos.value = []
  } finally {
    cargandoMenu.value = false
  }
}

const cargarCategorias = async () => {
  try {
    const response = await apiPublica.get('/categorias')
    categorias.value = response.data
  } catch (error) {
    console.error('Error cargando categorías:', error)
    categorias.value = []
  }
}

const cambiarCategoria = (categoriaId) => {
  categoriaActiva.value = categoriaId
  subcategoriaSeleccionada.value = 'todos'
}

const filtrarPorSubcategoria = (categoriaId) => {
  subcategoriaSeleccionada.value = categoriaId
}

const scrollToMenu = () => {
  menuSection.value?.scrollIntoView({ behavior: 'smooth' })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 0
  }).format(value || 0)
}

const getDefaultDescription = (nombre) => {
  const descripciones = {
    'Café Americano': 'Café americano clásico',
    'Cappuccino': 'Cappuccino con espuma de leche',
    'Croissant': 'Croissant de mantequilla'
  }
  return descripciones[nombre] || 'Producto artesanal de nuestra casa'
}

const tieneOpciones = (item) => {
  return item.nombre.includes('Café') || item.nombre.includes('Cappuccino')
}

const getOpcionesItem = (item) => {
  if (item.nombre.includes('Café Americano')) return ['Tamaño']
  if (item.nombre.includes('Cappuccino')) return ['Tamaño', 'Tipo de Leche']
  return []
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    cargarCategorias(),
    cargarProductos()
  ])
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════════════
   VARIABLES Y RESET
   ══════════════════════════════════════════════════════════════ */
:root {
  --cafe-oscuro: #2c1810;
  --cafe-medio: #8b5e3c;
  --cafe-claro: #c9a87c;
  --cafe-crema: #faf3e0;
  --cafe-dorado: #d4a373;
  --cafe-gris: #6b5a4c;
  --cafe-gris-claro: #e8e0d5;
  --cafe-sombra: rgba(44, 24, 16, 0.1);
  --cafe-sombra-md: rgba(44, 24, 16, 0.15);
}

.landing-page {
  width: 100%;
  overflow-x: hidden;
  background: var(--cafe-crema);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

/* ══════════════════════════════════════════════════════════════
   HERO SECTION
   ══════════════════════════════════════════════════════════════ */
.hero {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, 
    var(--cafe-oscuro) 0%, 
    #4a3428 50%, 
    var(--cafe-medio) 100%
  );
  overflow: hidden;
}

.hero__grain {
  position: absolute;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='1.5' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
  opacity: 0.05;
  pointer-events: none;
}

.hero__container {
  position: relative;
  text-align: center;
  z-index: 1;
  padding: 60px 24px;
}

.hero__brand {
  margin-bottom: 48px;
}

.hero__logo {
  width: 120px;
  height: 120px;
  margin: 0 auto 24px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  border-radius: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  border: 2px solid rgba(255,255,255,0.1);
}

.hero__title {
  font-size: 4rem;
  font-weight: 800;
  color: white;
  margin-bottom: 12px;
  letter-spacing: -2px;
  text-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.hero__subtitle {
  font-size: 1.2rem;
  color: var(--cafe-claro);
  font-weight: 300;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.hero__tagline {
  font-size: 1.3rem;
  line-height: 1.6;
  color: rgba(255,255,255,0.9);
  margin-bottom: 40px;
  font-weight: 300;
  max-width: 700px;
  margin-left: auto;
  margin-right: auto;
}

.hero__buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
}

.hero__stats {
  display: flex;
  justify-content: center;
  gap: 48px;
  margin-top: 60px;
  flex-wrap: wrap;
}

.stat-item {
  text-align: center;
  color: white;
}

.stat-value {
  display: block;
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--cafe-claro);
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* ══════════════════════════════════════════════════════════════
   MENU SECTION
   ══════════════════════════════════════════════════════════════ */
.menu {
  padding: 80px 0;
  background: white;
}

.section-title {
  font-size: 3rem;
  font-weight: 700;
  color: var(--cafe-oscuro);
  text-align: center;
  margin-bottom: 16px;
  letter-spacing: -1px;
}

.section-subtitle {
  font-size: 1.2rem;
  color: var(--cafe-gris);
  text-align: center;
  max-width: 600px;
  margin: 0 auto 40px;
}

.menu__categories-nav {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 32px;
  border-bottom: 2px solid var(--cafe-gris-claro);
  padding-bottom: 16px;
}

.category-nav-btn {
  padding: 12px 28px;
  background: transparent;
  border: none;
  color: var(--cafe-gris);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  font-size: 1.1rem;
}

.category-nav-btn::after {
  content: '';
  position: absolute;
  bottom: -18px;
  left: 0;
  width: 100%;
  height: 3px;
  background: var(--cafe-medio);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.category-nav-btn.active {
  color: var(--cafe-medio);
}

.category-nav-btn.active::after {
  transform: scaleX(1);
}

.menu__subcategories {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 48px;
}

.subcategory-btn {
  padding: 8px 20px;
  background: white;
  border: 2px solid var(--cafe-gris-claro);
  border-radius: 30px;
  color: var(--cafe-medio);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.25s ease;
}

.subcategory-btn:hover {
  border-color: var(--cafe-medio);
  transform: translateY(-2px);
}

.subcategory-btn.active {
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  color: white;
  border-color: transparent;
  box-shadow: 0 4px 16px var(--cafe-sombra-md);
}

.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 30px;
}

.menu-item {
  background: white;
  border: 2px solid var(--cafe-gris-claro);
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.3s ease;
  display: flex;
}

.menu-item:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 40px var(--cafe-sombra-md);
  border-color: var(--cafe-dorado);
}

.menu-item__image {
  width: 100px;
  background: linear-gradient(135deg, var(--cafe-crema), #fff);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--cafe-medio);
  border-right: 2px solid var(--cafe-gris-claro);
}

.menu-item__content {
  flex: 1;
  padding: 20px;
}

.menu-item__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.menu-item__name {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--cafe-oscuro);
}

.menu-item__price {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--cafe-medio);
}

.menu-item__description {
  color: var(--cafe-gris);
  font-size: 0.9rem;
  margin-bottom: 8px;
  line-height: 1.4;
}

.menu-item__rating {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 12px;
}

.stars {
  color: #fbbf24;
  font-size: 0.9rem;
}

.rating-value {
  color: var(--cafe-gris);
  font-size: 0.9rem;
}

.menu-item__options {
  margin-bottom: 16px;
}

.options-label {
  display: block;
  font-size: 0.85rem;
  color: var(--cafe-gris);
  margin-bottom: 6px;
  font-weight: 500;
}

.options-tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.option-tag {
  padding: 4px 12px;
  background: var(--cafe-crema);
  border-radius: 20px;
  font-size: 0.8rem;
  color: var(--cafe-medio);
  font-weight: 500;
}

.menu-item__btn {
  width: 100%;
  padding: 10px;
  background: linear-gradient(135deg, var(--cafe-medio), var(--cafe-claro));
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.menu-item__btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px var(--cafe-sombra-md);
}

/* ══════════════════════════════════════════════════════════════
   LOCATION SECTION
   ══════════════════════════════════════════════════════════════ */
.location {
  padding: 80px 0;
  background: linear-gradient(135deg, var(--cafe-crema), white);
}

.location__content {
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
}

.location__title {
  font-size: 2.8rem;
  font-weight: 700;
  color: var(--cafe-oscuro);
  margin-bottom: 16px;
}

.location__subtitle {
  font-size: 1.2rem;
  color: var(--cafe-gris);
  margin-bottom: 40px;
}

.location__actions {
  display: flex;
  gap: 20px;
  justify-content: center;
  margin-bottom: 60px;
}

.location__details {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
  text-align: left;
  margin-top: 40px;
  padding: 40px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 8px 30px var(--cafe-sombra);
}

.detail-block h4 {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--cafe-oscuro);
  margin-bottom: 16px;
  font-size: 1.1rem;
}

.detail-block p {
  color: var(--cafe-gris);
  margin-bottom: 4px;
  font-size: 0.95rem;
}

.detail-block p.small {
  font-size: 0.85rem;
  opacity: 0.8;
  margin-bottom: 8px;
}

.map-link {
  display: inline-block;
  margin-top: 12px;
  color: var(--cafe-medio);
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.map-link:hover {
  color: var(--cafe-oscuro);
}

/* ══════════════════════════════════════════════════════════════
   FOOTER
   ══════════════════════════════════════════════════════════════ */
.footer {
  padding: 60px 0 30px;
  background: var(--cafe-oscuro);
  color: white;
}

.footer__content {
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
}

.footer__brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-bottom: 24px;
}

.footer__brand .brand-name {
  font-size: 1.5rem;
  font-weight: 700;
  display: block;
}

.footer__brand .brand-tagline {
  font-size: 0.9rem;
  opacity: 0.7;
  display: block;
}

.footer__description {
  font-size: 1rem;
  opacity: 0.8;
  margin-bottom: 30px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

.footer__links {
  margin-bottom: 30px;
}

.footer__links a {
  color: white;
  text-decoration: none;
  opacity: 0.7;
  transition: opacity 0.3s ease;
  font-size: 0.9rem;
}

.footer__links a:hover {
  opacity: 1;
}

.footer__links .separator {
  margin: 0 15px;
  opacity: 0.3;
}

.footer__copy {
  font-size: 0.9rem;
  opacity: 0.5;
}

/* ══════════════════════════════════════════════════════════════
   BOTONES
   ══════════════════════════════════════════════════════════════ */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 32px;
  border: none;
  border-radius: 14px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  text-decoration: none;
  box-shadow: 0 4px 20px var(--cafe-sombra-md);
}

.btn--primary {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.btn--primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 30px rgba(245,158,11,0.4);
}

.btn--secondary {
  background: white;
  color: var(--cafe-medio);
  border: 2px solid var(--cafe-medio);
}

.btn--secondary:hover {
  background: var(--cafe-medio);
  color: white;
  transform: translateY(-3px);
}

.btn--large {
  padding: 20px 40px;
  font-size: 1.1rem;
}

/* ══════════════════════════════════════════════════════════════
   LOADING Y UTILIDADES
   ══════════════════════════════════════════════════════════════ */
.menu__loading,
.menu__empty {
  text-align: center;
  padding: 60px 20px;
  color: var(--cafe-gris);
}

.spinner {
  width: 50px;
  height: 50px;
  margin: 0 auto 20px;
  border: 4px solid var(--cafe-gris-claro);
  border-top-color: var(--cafe-medio);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ══════════════════════════════════════════════════════════════
   ANIMACIONES
   ══════════════════════════════════════════════════════════════ */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in-down {
  animation: fadeInDown 0.8s ease both;
}

.fade-in-up {
  animation: fadeInUp 0.8s ease both;
}

/* ══════════════════════════════════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
  .hero__title {
    font-size: 2.5rem;
  }

  .hero__tagline {
    font-size: 1.1rem;
  }

  .hero__buttons {
    flex-direction: column;
  }

  .hero__stats {
    gap: 24px;
  }

  .stat-value {
    font-size: 2rem;
  }

  .stat-label {
    font-size: 0.8rem;
  }

  .section-title {
    font-size: 2.2rem;
  }

  .menu-grid {
    grid-template-columns: 1fr;
  }

  .menu-item {
    flex-direction: column;
  }

  .menu-item__image {
    width: 100%;
    height: 80px;
    border-right: none;
    border-bottom: 2px solid var(--cafe-gris-claro);
  }

  .location__details {
    grid-template-columns: 1fr;
    padding: 30px;
  }

  .location__actions {
    flex-direction: column;
  }

  .footer__content {
    padding: 0 20px;
  }
}
</style>