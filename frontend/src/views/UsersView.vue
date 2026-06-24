<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import {
  Users,
  Plus,
  Pencil,
  Trash2,
  X,
  Key,
  Check,
  Eye,
  EyeOff,
  ExternalLink,
  Loader,
} from '@lucide/vue'

const users = ref([])
const loading = ref(true)
const showUserForm = ref(false)
const showApikeyForm = ref(false)
const editingUser = ref(null)
const selectedUser = ref(null)

const userForm = ref({
  email: '',
  password: '',
})

const apikeyValue = ref('')
const apikeyVisible = ref(false)
const apikeyFeedback = ref(null)
const savingApikey = ref(false)

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await api.get('/api/users')
    users.value = data.data
  } catch {
    //
  } finally {
    loading.value = false
  }
}

function openCreateUser() {
  editingUser.value = null
  userForm.value = { email: '', password: '' }
  showUserForm.value = true
}

function openEditUser(user) {
  editingUser.value = user
  userForm.value = { email: user.email, password: '' }
  showUserForm.value = true
}

async function handleSaveUser() {
  try {
    if (editingUser.value) {
      const payload = { email: userForm.value.email }
      if (userForm.value.password) payload.password = userForm.value.password
      await api.put(`/api/users/${editingUser.value.id}`, payload)
    } else {
      await api.post('/api/users', userForm.value)
    }
    showUserForm.value = false
    await fetchUsers()
  } catch {
    //
  }
}

async function handleDelete(user) {
  if (user.email === 'dail') return
  if (!confirm(`Delete user "${user.email}"?`)) return
  try {
    await api.delete(`/api/users/${user.id}`)
    await fetchUsers()
  } catch {
    //
  }
}

function openApikey(user) {
  selectedUser.value = user
  apikeyValue.value = user.whatsapp_apikey || ''
  apikeyVisible.value = false
  apikeyFeedback.value = null
  showApikeyForm.value = true
}

async function handleSaveApikey() {
  savingApikey.value = true
  apikeyFeedback.value = null
  try {
    await api.put(`/api/users/${selectedUser.value.id}/apikey`, {
      apikey: apikeyValue.value,
    })
    apikeyFeedback.value = { type: 'success', message: 'API key saved successfully' }
    selectedUser.value.whatsapp_apikey = apikeyValue.value
  } catch (err) {
    apikeyFeedback.value = { type: 'error', message: err.response?.data?.message || 'Failed to save' }
  } finally {
    savingApikey.value = false
  }
}

onMounted(fetchUsers)
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Users class="w-6 h-6 text-vue-green" />
        <h1 class="text-xl font-bold text-on-surface font-display">Users</h1>
      </div>
      <button
        @click="openCreateUser"
        class="flex items-center gap-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold px-4 py-2 rounded-lg text-sm transition cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        New User
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <Loader class="w-8 h-8 text-vue-green animate-spin" />
    </div>

    <div v-else-if="users.length === 0" class="text-center py-12 text-slate-text">
      <Users class="w-12 h-12 mx-auto mb-2 opacity-50" />
      <p>No users found</p>
    </div>

    <div v-else class="glass-panel rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-surface-container text-slate-text text-xs uppercase tracking-wider">
            <th class="text-left px-4 py-3 font-medium">Email</th>
            <th class="text-left px-4 py-3 font-medium">WhatsApp API Key</th>
            <th class="text-left px-4 py-3 font-medium">Created</th>
            <th class="text-left px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/10">
          <tr v-for="user in users" :key="user.id" class="hover:bg-surface-charcoal/50 transition">
            <td class="px-4 py-3">
              <span class="text-on-surface font-medium">{{ user.email }}</span>
            </td>
            <td class="px-4 py-3">
              <span
                class="inline-flex items-center gap-1 text-xs font-medium"
                :class="user.whatsapp_apikey ? 'text-success' : 'text-slate-text'"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="user.whatsapp_apikey ? 'bg-success' : 'bg-slate-text'" />
                {{ user.whatsapp_apikey ? 'Configured' : 'Not set' }}
              </span>
            </td>
            <td class="px-4 py-3 text-slate-text">
              {{ new Date(user.created_at).toLocaleDateString() }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  @click="openApikey(user)"
                  class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer"
                  :class="user.whatsapp_apikey
                    ? 'bg-vue-green/10 text-vue-green hover:bg-vue-green/20'
                    : 'bg-warning/10 text-warning hover:bg-warning/20'"
                >
                  <Key class="w-3 h-3" />
                  API Key
                </button>
                <button
                  @click="openEditUser(user)"
                  class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
                >
                  <Pencil class="w-4 h-4" />
                </button>
                <button
                  v-if="user.email !== 'dail'"
                  @click="handleDelete(user)"
                  class="p-1.5 text-slate-text hover:text-error rounded-lg hover:bg-error/10 transition cursor-pointer"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- User Create/Edit Modal -->
    <div v-if="showUserForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="glass-panel rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-on-surface font-display">
            {{ editingUser ? 'Edit User' : 'New User' }}
          </h2>
          <button @click="showUserForm = false" class="text-slate-text hover:text-on-surface transition cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Email</label>
            <input
              v-model="userForm.email"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="user@email.com"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">
              {{ editingUser ? 'New Password (leave empty to keep)' : 'Password' }}
            </label>
            <input
              v-model="userForm.password"
              type="password"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              :placeholder="editingUser ? 'Leave empty to keep current' : '••••••••'"
            />
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="showUserForm = false"
            class="px-4 py-2 text-sm text-slate-text hover:text-on-surface transition cursor-pointer"
          >Cancel</button>
          <button
            @click="handleSaveUser"
            :disabled="!userForm.email"
            class="px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
          >{{ editingUser ? 'Update' : 'Create' }}</button>
        </div>
      </div>
    </div>

    <!-- API Key Modal -->
    <div v-if="showApikeyForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="glass-panel rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-on-surface font-display">WhatsApp API Key</h2>
            <p class="text-sm text-slate-text mt-0.5">{{ selectedUser?.email }}</p>
          </div>
          <button @click="showApikeyForm = false" class="text-slate-text hover:text-on-surface transition cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="bg-surface-charcoal border border-outline-variant/30 rounded-lg p-4 mb-5 text-sm text-on-surface-variant space-y-2">
          <p class="font-medium text-on-surface">How to get your API key:</p>
          <ol class="list-decimal list-inside space-y-1">
            <li>Save <strong class="text-on-surface">+34 684 72 39 62</strong> to your contacts</li>
            <li>Send via WhatsApp: <code class="bg-surface-container-high px-1 rounded text-vue-green text-xs">I allow callmebot to send me messages</code></li>
            <li>You will receive an API key</li>
            <li>Copy and paste it below</li>
          </ol>
          <a
            href="https://www.callmebot.com/blog/free-api-whatsapp-messages/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1 text-vue-green hover:text-secondary transition mt-1"
          >
            View docs <ExternalLink class="w-3 h-3" />
          </a>
        </div>

        <div class="relative mb-4">
          <input
            v-model="apikeyValue"
            :type="apikeyVisible ? 'text' : 'password'"
            placeholder="Enter Callmebot API key"
            class="w-full px-4 py-2 pr-10 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition font-mono text-sm"
          />
          <button
            type="button"
            @click="apikeyVisible = !apikeyVisible"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-text hover:text-on-surface transition cursor-pointer"
          >
            <Eye v-if="!apikeyVisible" class="w-4 h-4" />
            <EyeOff v-else class="w-4 h-4" />
          </button>
        </div>

        <div
          v-if="apikeyFeedback"
          class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm mb-4"
          :class="apikeyFeedback.type === 'success'
            ? 'bg-success/10 text-success border border-success/20'
            : 'bg-error/10 text-error border border-error/20'"
        >
          <Check v-if="apikeyFeedback.type === 'success'" class="w-4 h-4 shrink-0" />
          <X v-else class="w-4 h-4 shrink-0" />
          {{ apikeyFeedback.message }}
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="showApikeyForm = false"
            class="px-4 py-2 text-sm text-slate-text hover:text-on-surface transition cursor-pointer"
          >Close</button>
          <button
            @click="handleSaveApikey"
            :disabled="savingApikey"
            class="px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
          >{{ savingApikey ? 'Saving...' : 'Save' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
