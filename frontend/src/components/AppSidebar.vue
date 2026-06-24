<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import {
  LayoutDashboard,
  Users,
  FileText,
  DollarSign,
  LogOut,
} from '@lucide/vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const navItems = [
  { name: 'Dashboard', route: 'Dashboard', icon: LayoutDashboard },
  { name: 'Leads', route: 'Leads', icon: Users },
  { name: 'Templates', route: 'Templates', icon: FileText },
  { name: 'Tiers', route: 'Tiers', icon: DollarSign },
  { name: 'Users', route: 'Users', icon: Users },
]

function isActive(item) {
  return route.name === item.route
}

async function handleLogout() {
  auth.logout()
  router.push({ name: 'Login' })
}
</script>

<template>
  <aside class="fixed top-0 left-0 z-40 w-64 h-screen glass-panel border-r border-outline-variant/30 flex flex-col">
    <router-link
      :to="{ name: 'Dashboard' }"
      class="flex items-center gap-3 px-6 pt-6 pb-5 border-b border-outline-variant/20"
    >
      <img src="/logo.webp" alt="vuno" class="h-8 w-auto" />
      <div class="flex flex-col">
        <div class="flex items-baseline gap-0.5">
          <span class="font-display text-lg font-bold text-on-surface tracking-tight">VUNO</span>
          <span class="font-display text-lg font-bold text-vue-green tracking-tight">LEED</span>
        </div>
        <span class="text-[10px] text-slate-text font-body uppercase tracking-widest">CRM</span>
      </div>
    </router-link>

    <nav class="flex-1 px-3 py-4 space-y-1">
      <router-link
        v-for="item in navItems"
        :key="item.route"
        :to="{ name: item.route }"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
        :class="isActive(item)
          ? 'bg-vue-green/10 text-vue-green border border-vue-green/20'
          : 'text-slate-text hover:text-on-surface hover:bg-surface-charcoal border border-transparent'"
      >
        <component :is="item.icon" class="w-5 h-5 shrink-0" />
        {{ item.name }}
      </router-link>
    </nav>

    <div class="px-3 py-4 border-t border-outline-variant/20">
      <div class="flex items-center gap-3 px-3 py-2 mb-2">
        <div class="w-8 h-8 rounded-full bg-vue-green/20 flex items-center justify-center">
          <span class="text-sm font-bold text-vue-green">
            {{ auth.user?.email?.charAt(0).toUpperCase() }}
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-on-surface truncate">
            {{ auth.user?.email }}
          </p>
        </div>
      </div>
      <button
        @click="handleLogout"
        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-slate-text hover:text-error hover:bg-error/5 border border-transparent hover:border-error/20 transition-all duration-200 cursor-pointer"
      >
        <LogOut class="w-5 h-5 shrink-0" />
        Logout
      </button>
    </div>
  </aside>
</template>
