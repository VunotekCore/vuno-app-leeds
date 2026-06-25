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
  <div class="min-h-screen bg-surface flex overflow-x-hidden max-w-full">
    <AppSidebar v-if="auth.isAuthenticated" :is-open="sidebarOpen" @toggle="toggleSidebar" />

    <div class="flex-1 flex flex-col min-h-screen max-w-full min-w-0">
      <header
        v-if="auth.isAuthenticated"
        class="md:hidden fixed top-0 left-0 right-0 z-20 h-14 glass-panel border-b border-outline-variant/20 flex items-center gap-3 px-4"
      >
        <button
          @click="toggleSidebar"
          class="p-2 bg-surface-charcoal border border-outline-variant/30 rounded-lg text-slate-text hover:text-on-surface transition cursor-pointer"
          aria-label="Toggle sidebar"
        >
          <Menu class="w-5 h-5" />
        </button>
        <span class="font-display text-sm font-bold text-on-surface tracking-tight">VUNO</span>
        <span class="font-display text-sm font-bold text-vue-green tracking-tight">LEED</span>
      </header>

      <main
        class="flex-1 min-h-screen transition-all duration-300 max-w-full min-w-0"
        :class="auth.isAuthenticated ? 'pt-14 md:pt-0 md:ml-64' : ''"
      >
        <router-view />
      </main>
    </div>
  </div>
</template>
