<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { FolderKanban, Plus, Pencil, Trash2, X } from '@lucide/vue'

const categories = ref([])
const loading = ref(true)
const showForm = ref(false)
const editingCategory = ref(null)

const form = ref({
  name: '',
})

async function fetchCategories() {
  loading.value = true
  try {
    const { data } = await api.get('/api/categories')
    categories.value = data.data
  } catch {
    //
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingCategory.value = null
  form.value = { name: '' }
  showForm.value = true
}

function openEdit(category) {
  editingCategory.value = category
  form.value = { name: category.name }
  showForm.value = true
}

async function handleSave() {
  try {
    if (editingCategory.value) {
      await api.put(`/api/categories/${editingCategory.value.id}`, form.value)
    } else {
      await api.post('/api/categories', form.value)
    }
    showForm.value = false
    await fetchCategories()
  } catch {
    //
  }
}

async function handleDelete(category) {
  if (!confirm(`Delete category "${category.name}"?`)) return
  try {
    await api.delete(`/api/categories/${category.id}`)
    await fetchCategories()
  } catch {
    //
  }
}

onMounted(fetchCategories)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <FolderKanban class="w-6 h-6 text-vue-green shrink-0" />
        <h1 class="text-lg sm:text-xl font-bold text-on-surface font-display">Categories</h1>
      </div>
      <button
        @click="openCreate"
        class="flex items-center gap-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold px-4 py-2 rounded-lg text-sm transition cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        New Category
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-outline-variant border-t-vue-green rounded-full animate-spin" />
    </div>

    <div v-else-if="categories.length === 0" class="text-center py-12 text-slate-text">
      <FolderKanban class="w-12 h-12 mx-auto mb-2 opacity-50" />
      <p>No categories yet. Create your first category.</p>
    </div>

    <template v-else>
    <div class="hidden md:block glass-panel rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-surface-container text-slate-text text-xs uppercase tracking-wider">
              <th class="text-left px-4 py-3 font-medium">Name</th>
              <th class="text-left px-4 py-3 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr v-for="category in categories" :key="category.id" class="hover:bg-surface-charcoal/50 transition">
              <td class="px-4 py-3">
                <span class="text-on-surface font-medium">{{ category.name }}</span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    @click="openEdit(category)"
                    class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button
                    @click="handleDelete(category)"
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

    <div class="md:hidden grid gap-3 sm:grid-cols-2">
      <div
        v-for="category in categories"
        :key="category.id"
        class="glass-panel rounded-xl p-4 transition hover:border-vue-green/30"
      >
        <div class="mb-2">
          <h3 class="text-sm font-semibold text-on-surface font-display truncate">{{ category.name }}</h3>
        </div>
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/10">
          <button
            @click="openEdit(category)"
            class="p-2.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
          >
            <Pencil class="w-5 h-5" />
          </button>
          <button
            @click="handleDelete(category)"
            class="p-2.5 text-slate-text hover:text-error rounded-lg hover:bg-error/10 transition cursor-pointer"
          >
            <Trash2 class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
    </template>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="glass-panel rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-on-surface font-display">
            {{ editingCategory ? 'Edit Category' : 'New Category' }}
          </h2>
          <button @click="showForm = false" class="text-slate-text hover:text-on-surface transition cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Category Name</label>
            <input
              v-model="form.name"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="e.g., Clothing Store"
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
            {{ editingCategory ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
