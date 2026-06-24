<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { FileText, Plus, Pencil, Trash2, X } from '@lucide/vue'

const templates = ref([])
const products = ref([])
const loading = ref(true)
const showForm = ref(false)
const editingTemplate = ref(null)

const form = ref({
  template_name: '',
  message_body: '',
  product_id: null,
})

async function fetchTemplates() {
  loading.value = true
  try {
    const { data } = await api.get('/api/templates')
    templates.value = data.data
  } catch {
    //
  } finally {
    loading.value = false
  }
}

async function fetchProducts() {
  try {
    const { data } = await api.get('/api/products')
    products.value = data.data
  } catch {
    //
  }
}

function openCreate() {
  editingTemplate.value = null
  form.value = { template_name: '', message_body: '', product_id: null }
  showForm.value = true
}

function openEdit(template) {
  editingTemplate.value = template
  form.value = {
    template_name: template.template_name,
    message_body: template.message_body,
    product_id: template.product_id || null,
  }
  showForm.value = true
}

async function handleSave() {
  try {
    if (editingTemplate.value) {
      await api.put(`/api/templates/${editingTemplate.value.id}`, form.value)
    } else {
      await api.post('/api/templates', form.value)
    }
    showForm.value = false
    await fetchTemplates()
  } catch {
    //
  }
}

async function handleDelete(template) {
  if (!confirm(`Delete template "${template.template_name}"?`)) return
  try {
    await api.delete(`/api/templates/${template.id}`)
    await fetchTemplates()
  } catch {
    //
  }
}

onMounted(() => {
  fetchTemplates()
  fetchProducts()
})
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <FileText class="w-6 h-6 text-vue-green" />
        <h1 class="text-xl font-bold text-on-surface font-display">Message Templates</h1>
      </div>
      <button
        @click="openCreate"
        class="flex items-center gap-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold px-4 py-2 rounded-lg text-sm transition cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        New Template
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-outline-variant border-t-vue-green rounded-full animate-spin" />
    </div>

    <div v-else-if="templates.length === 0" class="text-center py-12 text-slate-text">
      <FileText class="w-12 h-12 mx-auto mb-2 opacity-50" />
      <p>No templates yet. Create your first message template.</p>
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="template in templates"
        :key="template.id"
        class="glass-panel rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:shadow-vue-green/5"
      >
        <div class="flex items-start justify-between mb-3">
          <div>
            <h3 class="font-semibold text-on-surface font-display">{{ template.template_name }}</h3>
            <span
              v-if="template.product_name"
              class="inline-block mt-1 text-xs bg-vue-green/10 text-vue-green px-2 py-0.5 rounded-full font-medium"
            >
              {{ template.product_name }}
            </span>
            <span
              v-else
              class="inline-block mt-1 text-xs bg-surface-charcoal text-slate-text px-2 py-0.5 rounded-full"
            >
              Global
            </span>
          </div>
          <div class="flex gap-1">
            <button
              @click="openEdit(template)"
              class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
            >
              <Pencil class="w-4 h-4" />
            </button>
            <button
              @click="handleDelete(template)"
              class="p-1.5 text-slate-text hover:text-error rounded-lg hover:bg-error/10 transition cursor-pointer"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
        <p class="text-sm text-on-surface-variant whitespace-pre-wrap line-clamp-4">{{ template.message_body }}</p>
        <div class="mt-3 text-xs text-slate-text">
          Available tags: <code class="bg-surface-charcoal px-1 rounded">[StoreName]</code> <code class="bg-surface-charcoal px-1 rounded">[TierPrice]</code> <code class="bg-surface-charcoal px-1 rounded">[ProductName]</code>
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="glass-panel rounded-xl shadow-xl p-6 w-full max-w-lg mx-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-on-surface font-display">
            {{ editingTemplate ? 'Edit Template' : 'New Template' }}
          </h2>
          <button @click="showForm = false" class="text-slate-text hover:text-on-surface transition cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Template Name</label>
            <input
              v-model="form.template_name"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="e.g., Initial Outreach"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Message Body</label>
            <textarea
              v-model="form.message_body"
              rows="5"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition resize-none"
              placeholder="Hi [StoreName], we have a special [TierPrice] offer for you!"
            />
            <p class="text-xs text-slate-text mt-1">
              Use <code class="bg-surface-charcoal px-1 rounded">[StoreName]</code>, <code class="bg-surface-charcoal px-1 rounded">[TierPrice]</code> and <code class="bg-surface-charcoal px-1 rounded">[ProductName]</code> as placeholders.
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Product (optional)</label>
            <select
              v-model="form.product_id"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
            >
              <option :value="null">Global template</option>
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }}
              </option>
            </select>
            <p class="text-xs text-slate-text mt-1">
              Leave as "Global" to make this template available for all leads.
            </p>
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
            :disabled="!form.template_name || !form.message_body"
            class="px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
          >
            {{ editingTemplate ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
