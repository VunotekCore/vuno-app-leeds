<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { Package, Plus, Pencil, Trash2, X } from '@lucide/vue'

const products = ref([])
const loading = ref(true)
const showForm = ref(false)
const editingProduct = ref(null)

const form = ref({
  name: '',
  description: '',
  base_price: null,
})

async function fetchProducts() {
  loading.value = true
  try {
    const { data } = await api.get('/api/products')
    products.value = data.data
  } catch {
    //
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingProduct.value = null
  form.value = { name: '', description: '', base_price: null }
  showForm.value = true
}

function openEdit(product) {
  editingProduct.value = product
  form.value = {
    name: product.name,
    description: product.description || '',
    base_price: product.base_price,
  }
  showForm.value = true
}

async function handleSave() {
  try {
    if (editingProduct.value) {
      await api.put(`/api/products/${editingProduct.value.id}`, form.value)
    } else {
      await api.post('/api/products', form.value)
    }
    showForm.value = false
    await fetchProducts()
  } catch {
    //
  }
}

async function handleDelete(product) {
  if (!confirm(`Delete product "${product.name}"?`)) return
  try {
    await api.delete(`/api/products/${product.id}`)
    await fetchProducts()
  } catch {
    //
  }
}

onMounted(fetchProducts)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Package class="w-6 h-6 text-vue-green shrink-0" />
        <h1 class="text-lg sm:text-xl font-bold text-on-surface font-display">Products</h1>
      </div>
      <button
        @click="openCreate"
        class="flex items-center gap-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold px-4 py-2 rounded-lg text-sm transition cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        New Product
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-outline-variant border-t-vue-green rounded-full animate-spin" />
    </div>

    <div v-else-if="products.length === 0" class="text-center py-12 text-slate-text">
      <Package class="w-12 h-12 mx-auto mb-2 opacity-50" />
      <p>No products yet. Create your first product.</p>
    </div>

    <template v-else>
    <!-- Desktop: table -->
    <div class="hidden lg:block glass-panel rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-surface-container text-slate-text text-xs uppercase tracking-wider">
              <th class="text-left px-4 py-3 font-medium">Name</th>
              <th class="text-left px-4 py-3 font-medium">Description</th>
              <th class="text-left px-4 py-3 font-medium">Base Price</th>
              <th class="text-left px-4 py-3 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr v-for="product in products" :key="product.id" class="hover:bg-surface-charcoal/50 transition">
              <td class="px-4 py-3">
                <span class="text-on-surface font-medium">{{ product.name }}</span>
              </td>
              <td class="px-4 py-3 text-sm text-on-surface-variant max-w-xs truncate">
                {{ product.description || '-' }}
              </td>
              <td class="px-4 py-3 text-on-surface-variant">
                <span v-if="product.base_price" class="font-medium text-vue-green">
                  ${{ parseFloat(product.base_price).toFixed(2) }} USD
                </span>
                <span v-else class="text-slate-text">-</span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    @click="openEdit(product)"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold bg-vue-green/10 text-vue-green hover:bg-vue-green/20 rounded-lg transition cursor-pointer"
                  >
                    <Pencil class="w-3 h-3" />
                    Edit
                  </button>
                  <button
                    @click="handleDelete(product)"
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
    </div>

    <!-- Mobile/tablet: cards -->
    <div class="lg:hidden grid gap-3 sm:grid-cols-2">
      <div
        v-for="product in products"
        :key="product.id"
        class="glass-panel rounded-xl p-4 transition hover:border-vue-green/30"
      >
        <div class="flex items-start justify-between gap-3 mb-2">
          <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-on-surface font-display truncate">{{ product.name }}</h3>
            <p v-if="product.base_price" class="text-lg font-bold text-vue-green mt-0.5 font-display">
              ${{ parseFloat(product.base_price).toFixed(2) }} USD
            </p>
          </div>
          <div class="flex gap-1 shrink-0">
            <button
              @click="openEdit(product)"
              class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
            >
              <Pencil class="w-4 h-4" />
            </button>
            <button
              @click="handleDelete(product)"
              class="p-1.5 text-slate-text hover:text-error rounded-lg hover:bg-error/10 transition cursor-pointer"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
        <p v-if="product.description" class="text-xs text-on-surface-variant line-clamp-2">{{ product.description }}</p>
      </div>
    </div>
    </template>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="glass-panel rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-on-surface font-display">
            {{ editingProduct ? 'Edit Product' : 'New Product' }}
          </h2>
          <button @click="showForm = false" class="text-slate-text hover:text-on-surface transition cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Product Name</label>
            <input
              v-model="form.name"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="e.g., SEO Consultation"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition resize-none"
              placeholder="Brief description of the product..."
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Base Price (optional)</label>
            <input
              v-model.number="form.base_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="99.00"
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
            :disabled="!form.name"
            class="px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
          >
            {{ editingProduct ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
