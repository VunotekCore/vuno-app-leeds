<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { Eye, EyeOff } from '@lucide/vue'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)

async function handleSubmit() {
  if (!email.value || !password.value) {
    error.value = 'Email and password are required'
    return
  }

  loading.value = true
  error.value = ''

  try {
    await auth.login(email.value, password.value)
    router.push({ name: 'Dashboard' })
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-surface">
    <div class="w-full max-w-md mx-4">
      <div class="glass-panel rounded-2xl p-8">
        <div class="text-center mb-8">
          <img src="/logo.webp" alt="vuno" class="h-16 w-auto mx-auto mb-4" />
          <div class="flex items-baseline justify-center gap-0.5">
            <h1 class="font-display text-2xl font-bold text-on-surface tracking-tight">VUNO</h1>
            <h1 class="font-display text-2xl font-bold text-vue-green tracking-tight">LEED</h1>
          </div>
          <p class="text-sm text-slate-text mt-1">Lead Management CRM</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Email</label>
            <input
              v-model="email"
              type="text"
              autocomplete="username"
              placeholder="dail"
              class="w-full px-4 py-2.5 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Password</label>
            <div class="relative">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 pr-10 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-text hover:text-on-surface transition cursor-pointer"
              >
                <Eye v-if="!showPassword" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div
            v-if="error"
            class="bg-error-container/20 border border-error/30 text-error px-4 py-2.5 rounded-lg text-sm"
          >
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold py-2.5 px-4 rounded-lg transition cursor-pointer"
          >
            {{ loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
