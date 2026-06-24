<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import {
  LayoutDashboard,
  Users,
  FileText,
  DollarSign,
  Package,
  FolderKanban,
  Settings,
  ChevronDown,
  ChevronRight,
  X,
  LogOut,
} from '@lucide/vue'

defineProps({
  isOpen: { type: Boolean, default: false },
})

const emit = defineEmits(['toggle'])

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const settingsOpen = ref(true)

const mainNav = [
  { name: 'Dashboard', route: 'Dashboard', icon: LayoutDashboard },
  { name: 'Leads', route: 'Leads', icon: Users },
  { name: 'Products', route: 'Products', icon: Package },
]

const settingsItems = [
  { name: 'Categories', route: 'Categories', icon: FolderKanban },
  { name: 'Tiers', route: 'Tiers', icon: DollarSign },
  { name: 'Templates', route: 'Templates', icon: FileText },
]

const isInSettings = computed(() =>
  settingsItems.some(item => route.name === item.route)
)

function isActive(item) {
  return route.name === item.route
}

function isAnySettingsActive() {
  return isInSettings.value
}

async function handleLogout() {
  close()
  auth.logout()
  router.push({ name: 'Login' })
}

function close() {
  emit('toggle')
}
</script>

<template>
  <div @click.self="close" v-if="isOpen" class="fixed inset-0 z-30 bg-black/50 lg:hidden" />

  <aside
    class="fixed top-0 left-0 z-40 w-64 h-screen glass-panel border-r border-outline-variant/30 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0"
    :class="{ 'translate-x-0': isOpen }"
  >
    <div class="flex items-center justify-between px-6 pt-6 pb-5 border-b border-outline-variant/20">
      <router-link
        :to="{ name: 'Dashboard' }"
        @click="close"
        class="flex items-center gap-3"
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
      <button @click="close" class="lg:hidden p-1 text-slate-text hover:text-on-surface transition cursor-pointer" aria-label="Close sidebar">
        <X class="w-5 h-5" />
      </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in mainNav"
        :key="item.route"
        :to="{ name: item.route }"
        @click="close"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
        :class="isActive(item)
          ? 'bg-vue-green/10 text-vue-green border border-vue-green/20'
          : 'text-slate-text hover:text-on-surface hover:bg-surface-charcoal border border-transparent'"
      >
        <component :is="item.icon" class="w-5 h-5 shrink-0" />
        {{ item.name }}
      </router-link>

      <div class="pt-2">
        <button
          @click="settingsOpen = !settingsOpen"
          class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer"
          :class="isAnySettingsActive()
            ? 'bg-vue-green/10 text-vue-green'
            : 'text-slate-text hover:text-on-surface hover:bg-surface-charcoal'"
        >
          <div class="flex items-center gap-3">
            <Settings class="w-5 h-5 shrink-0" />
            <span>Settings</span>
          </div>
          <component :is="settingsOpen ? ChevronDown : ChevronRight" class="w-4 h-4" />
        </button>

        <div v-if="settingsOpen" class="ml-2 mt-1 space-y-0.5">
          <router-link
            v-for="item in settingsItems"
            :key="item.route"
            :to="{ name: item.route }"
            @click="close"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200"
            :class="isActive(item)
              ? 'bg-vue-green/10 text-vue-green border border-vue-green/20'
              : 'text-slate-text hover:text-on-surface hover:bg-surface-charcoal border border-transparent'"
          >
            <component :is="item.icon" class="w-4 h-4 shrink-0" />
            {{ item.name }}
          </router-link>
        </div>
      </div>
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
