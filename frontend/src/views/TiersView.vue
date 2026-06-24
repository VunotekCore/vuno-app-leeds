<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { DollarSign, Plus, Pencil, Trash2, X } from '@lucide/vue'

const tiers = ref([])
const loading = ref(true)
const showForm = ref(false)
const editingTier = ref(null)

const form = ref({
  name: '',
  price: null,
})

async function fetchTiers() {
  loading.value = true
  try {
    const { data } = await api.get('/api/tiers')
    tiers.value = data.data
  } catch {
    //
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingTier.value = null
  form.value = { name: '', price: null }
  showForm.value = true
}

function openEdit(tier) {
  editingTier.value = tier
  form.value = {
    name: tier.name,
    price: tier.price,
  }
  showForm.value = true
}

async function handleSave() {
  try {
    if (editingTier.value) {
      await api.put(`/api/tiers/${editingTier.value.id}`, form.value)
    } else {
      await api.post('/api/tiers', form.value)
    }
    showForm.value = false
    await fetchTiers()
  } catch {
    //
  }
}

async function handleDelete(tier) {
  if (!confirm(`Delete tier "${tier.name}"?`)) return
  try {
    await api.delete(`/api/tiers/${tier.id}`)
    await fetchTiers()
  } catch {
    //
  }
}

onMounted(fetchTiers)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <DollarSign class="w-6 h-6 text-vue-green shrink-0" />
        <h1 class="text-lg sm:text-xl font-bold text-on-surface font-display">Tiers</h1>
      </div>
      <button
        @click="openCreate"
        class="flex items-center gap-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold px-4 py-2 rounded-lg text-sm transition cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        New Tier
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-outline-variant border-t-vue-green rounded-full animate-spin" />
    </div>

    <div v-else-if="tiers.length === 0" class="text-center py-12 text-slate-text">
      <DollarSign class="w-12 h-12 mx-auto mb-2 opacity-50" />
      <p>No tiers configured. Create your first tier.</p>
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="tier in tiers"
        :key="tier.id"
        class="glass-panel rounded-xl p-4 transition-all duration-300 hover:border-vue-green/30"
      >
        <div class="mb-2">
          <h3 class="font-semibold text-on-surface font-display text-sm">{{ tier.name }}</h3>
          <p class="text-xl font-bold text-vue-green mt-1 font-display">
            ${{ parseFloat(tier.price).toFixed(2) }} USD
          </p>
        </div>
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-outline-variant/10">
          <button
            @click="openEdit(tier)"
            class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
          >
            <Pencil class="w-4 h-4" />
          </button>
          <button
            @click="handleDelete(tier)"
            class="p-1.5 text-slate-text hover:text-error rounded-lg hover:bg-error/10 transition cursor-pointer"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="glass-panel rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-on-surface font-display">
            {{ editingTier ? 'Edit Tier' : 'New Tier' }}
          </h2>
          <button @click="showForm = false" class="text-slate-text hover:text-on-surface transition cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Tier Name</label>
            <input
              v-model="form.name"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="e.g., Tier 1"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Price (USD)</label>
            <input
              v-model.number="form.price"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="50.00"
            />
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="showForm = false"
            class="px-4 py-2 text-sm text-slate-text hover:text-on-surface transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="handleSave"
            :disabled="!form.name || form.price === null"
            class="px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
          >
            {{ editingTier ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
