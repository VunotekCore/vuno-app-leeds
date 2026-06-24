<script setup>
import { useAuthStore } from './stores/auth'
import AppSidebar from './components/AppSidebar.vue'

const auth = useAuthStore()
</script>

<template>
  <div class="min-h-screen bg-surface flex">
    <AppSidebar v-if="auth.isAuthenticated" />
    <main
      class="flex-1 min-h-screen transition-all duration-300"
      :class="auth.isAuthenticated ? 'ml-64' : ''"
    >
      <router-view />
    </main>
  </div>
</template>
