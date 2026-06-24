<script setup>
import { ref } from 'vue'
import { useAuthStore } from './stores/auth'
import AppSidebar from './components/AppSidebar.vue'
import { Menu } from '@lucide/vue'

const auth = useAuthStore()
const sidebarOpen = ref(false)

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}
</script>

<template>
  <div class="min-h-screen bg-surface flex">
    <AppSidebar v-if="auth.isAuthenticated" :is-open="sidebarOpen" @toggle="toggleSidebar" />
    <main
      class="flex-1 min-h-screen transition-all duration-300"
      :class="auth.isAuthenticated ? 'lg:ml-64' : ''"
    >
      <button
        v-if="auth.isAuthenticated"
        @click="toggleSidebar"
        class="fixed top-4 left-4 z-30 lg:hidden p-2 bg-surface-charcoal border border-outline-variant/30 rounded-lg text-slate-text hover:text-on-surface transition cursor-pointer"
        aria-label="Toggle sidebar"
      >
        <Menu class="w-5 h-5" />
      </button>
      <router-view />
    </main>
  </div>
</template>
