<template>
  <div>
    <!-- 🔹 Sidebar desktop -->
    <aside class="sidebar--desktop">
      <nav class="sidebar__nav">
        <router-link
          v-for="item in menuItems"
          :key="item.ruta"
          :to="item.ruta"
          custom
          v-slot="{ isActive, navigate }"
        >
          <button
            class="sidebar__item"
            :class="{ 'sidebar__item--active': isActive }"
            @click="navigate"
          >
            <i :class="`bi ${item.icono}`"></i>
            <span>{{ item.nombre }}</span>
          </button>
        </router-link>
      </nav>
    </aside>

    <!-- 🔹 Overlay móvil -->
    <transition name="overlay">
      <div
        v-if="abierto"
        class="mobile-overlay"
        @click="$emit('cerrar')"
      />
    </transition>

    <!-- 🔹 Sidebar móvil -->
    <transition name="slide">
      <aside v-if="abierto" class="sidebar--mobile">
        <div class="sidebar__mobile-header">
          <span class="sidebar__mobile-title">Café Artesano</span>
          <button class="sidebar__mobile-close" @click="$emit('cerrar')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <nav class="sidebar__nav">
          <router-link
            v-for="item in menuItems"
            :key="item.ruta"
            :to="item.ruta"
            custom
            v-slot="{ isActive, navigate }"
          >
            <button
              class="sidebar__item"
              :class="{ 'sidebar__item--active': isActive }"
              @click="navigate(); $emit('cerrar')"
            >
              <i :class="`bi ${item.icono}`"></i>
              <span>{{ item.nombre }}</span>
            </button>
          </router-link>
        </nav>
      </aside>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'AppSidebar',
  props: {
    abierto: {
      type: Boolean,
      default: false
    },
    menuItems: {
      type: Array,
      default: () => []
    }
  },
  emits: ['cerrar']
}
</script>
