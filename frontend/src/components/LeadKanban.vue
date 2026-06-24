<script setup>
import { ref, computed } from 'vue'
import {
  Smartphone, Pencil, Trash2, MessageSquare, ChevronDown, ChevronRight, FileText,
} from '@lucide/vue'

const props = defineProps({
  leads: { type: Array, default: () => [] },
  templates: { type: Array, default: () => [] },
  products: { type: Array, default: () => [] },
  search: { type: String, default: '' },
})

const emit = defineEmits(['updateStatus', 'edit', 'delete', 'contact', 'sendWhatsApp', 'notes'])

const statusColumns = [
  { key: 'Pending', label: 'Pending', color: 'border-l-yellow-500' },
  { key: 'First Contact', label: 'First Contact', color: 'border-l-blue-500' },
  { key: 'Second Contact', label: 'Second Contact', color: 'border-l-purple-500' },
  { key: 'Interested', label: 'Interested', color: 'border-l-emerald-500' },
]

const filteredLeads = computed(() => {
  if (!props.search) return props.leads
  const q = props.search.toLowerCase()
  return props.leads.filter((l) =>
    l.store_name?.toLowerCase().includes(q) ||
    l.phone?.includes(q) ||
    l.email?.toLowerCase().includes(q) ||
    l.product_name?.toLowerCase().includes(q) ||
    l.category_name?.toLowerCase().includes(q)
  )
})

function leadsByStatus(key) {
  return filteredLeads.value.filter((l) => l.contact_status === key)
}

let draggedId = null

function onDragStart(e, leadId) {
  draggedId = leadId
  e.dataTransfer.effectAllowed = 'move'
  e.dataTransfer.setData('text/plain', leadId)
}

function onDragOver(e) {
  e.preventDefault()
  e.dataTransfer.dropEffect = 'move'
}

function onDrop(e, newStatus) {
  e.preventDefault()
  if (!draggedId) return
  const lead = props.leads.find((l) => l.id === draggedId)
  if (lead && lead.contact_status !== newStatus) {
    emit('updateStatus', { ...lead, contact_status: newStatus })
  }
  draggedId = null
}

function getTemplateName(lead) {
  if (!lead.selected_template_id) return null
  const tpl = props.templates.find((t) => t.id === lead.selected_template_id)
  return tpl?.template_name || null
}

const expandedColumn = ref(statusColumns[0].key)

function toggleColumn(key) {
  expandedColumn.value = expandedColumn.value === key ? null : key
}
</script>

<template>
  <!-- Desktop: Kanban columns -->
  <div class="hidden md:flex gap-4 overflow-x-auto pb-4 min-h-[60vh] snap-x snap-mandatory">
    <div
      v-for="col in statusColumns"
      :key="col.key"
      class="w-[260px] sm:w-[280px] shrink-0 snap-start bg-surface-charcoal/50 rounded-xl border border-outline-variant/20 flex flex-col"
      @dragover="onDragOver"
      @drop="(e) => onDrop(e, col.key)"
    >
      <div class="flex items-center justify-between px-2.5 sm:px-4 py-2 sm:py-3 border-b border-outline-variant/10">
        <div class="flex items-center gap-1.5 sm:gap-2">
          <div class="w-1.5 sm:w-2 h-1.5 sm:h-2 rounded-full shrink-0" :class="col.color.replace('border-l-', 'bg-')" />
          <h3 class="text-xs sm:text-sm font-semibold text-on-surface">{{ col.label }}</h3>
        </div>
        <span class="text-xs text-slate-text bg-surface-charcoal px-1.5 sm:px-2 py-0.5 rounded-full">
          {{ leadsByStatus(col.key).length }}
        </span>
      </div>

      <div class="flex-1 p-2 sm:p-3 space-y-2 sm:space-y-3 overflow-y-auto">
        <div
          v-for="lead in leadsByStatus(col.key)"
          :key="lead.id"
          :draggable="true"
          @dragstart="(e) => onDragStart(e, lead.id)"
          class="glass-panel rounded-lg p-2 sm:p-3 border border-outline-variant/10 cursor-grab active:cursor-grabbing hover:border-vue-green/30 transition-all duration-150 space-y-2"
        >
          <!-- Header -->
          <div class="mb-1">
            <p class="text-sm font-semibold text-on-surface truncate">{{ lead.store_name }}</p>
            <p class="text-xs text-slate-text truncate">{{ lead.phone ? `+${lead.phone}` : '-' }}</p>
          </div>

          <!-- Body -->
          <div v-if="lead.product_name || lead.tier_classification || lead.category_name" class="flex flex-wrap gap-1">
            <span v-if="lead.product_name" class="text-xs bg-vue-green/10 text-vue-green px-1.5 py-0.5 rounded font-medium">
              {{ lead.product_name }}
            </span>
            <span v-if="lead.tier_classification" class="text-xs bg-surface-charcoal text-slate-text px-1.5 py-0.5 rounded">
              {{ lead.tier_classification }}
            </span>
            <span v-if="lead.category_name" class="text-xs bg-indigo-500/10 text-indigo-400 px-1.5 py-0.5 rounded font-medium">
              {{ lead.category_name }}
            </span>
          </div>

          <div v-if="lead.email" class="text-xs text-slate-text truncate">
            {{ lead.email }}
          </div>

          <div class="flex items-center justify-between text-xs text-slate-text">
            <span v-if="lead.last_contact_date">
              {{ new Date(lead.last_contact_date).toLocaleDateString() }}
            </span>
            <span v-if="getTemplateName(lead)" class="truncate max-w-[120px]">
              {{ getTemplateName(lead) }}
            </span>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-1 pt-2 border-t border-outline-variant/10">
            <button
              @click.stop="emit('notes', lead)"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition cursor-pointer"
              title="Notes"
            >
              <FileText class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('sendWhatsApp', lead)"
              :disabled="!lead.selected_template_id"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
              title="Send WhatsApp"
            >
              <Smartphone class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('contact', lead)"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition cursor-pointer"
              title="Contact modal"
            >
              <MessageSquare class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('edit', lead)"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition cursor-pointer"
              title="Edit"
            >
              <Pencil class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('delete', lead)"
              class="p-1 text-slate-text hover:text-error rounded hover:bg-error/10 transition cursor-pointer"
              title="Delete"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <div
          v-if="leadsByStatus(col.key).length === 0"
          class="flex items-center justify-center py-6 sm:py-8 text-xs text-slate-text/50 border-2 border-dashed border-outline-variant/10 rounded-lg"
        >
          Drop leads here
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile: accordion -->
  <div class="md:hidden space-y-2">
    <div
      v-for="col in statusColumns"
      :key="col.key"
      class="glass-panel rounded-xl overflow-hidden"
    >
      <button
        @click="toggleColumn(col.key)"
        class="flex items-center justify-between w-full px-4 py-3 text-left cursor-pointer transition hover:bg-surface-charcoal/50"
      >
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 rounded-full" :class="col.color.replace('border-l-', 'bg-')" />
          <span class="text-sm font-semibold text-on-surface">{{ col.label }}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs text-slate-text bg-surface-charcoal px-2 py-0.5 rounded-full">
            {{ leadsByStatus(col.key).length }}
          </span>
          <ChevronDown
            v-if="expandedColumn === col.key"
            class="w-4 h-4 text-slate-text transition"
          />
          <ChevronRight
            v-else
            class="w-4 h-4 text-slate-text transition"
          />
        </div>
      </button>

      <div v-if="expandedColumn === col.key" class="px-3 pb-3 space-y-2">
        <div
          v-for="lead in leadsByStatus(col.key)"
          :key="lead.id"
          class="glass-panel rounded-lg p-3 border border-outline-variant/10 space-y-2"
        >
          <div class="mb-1">
            <p class="text-sm font-semibold text-on-surface truncate">{{ lead.store_name }}</p>
            <p class="text-xs text-slate-text truncate">{{ lead.phone ? `+${lead.phone}` : '-' }}</p>
          </div>

          <div v-if="lead.product_name || lead.tier_classification || lead.category_name" class="flex flex-wrap gap-1">
            <span v-if="lead.product_name" class="text-xs bg-vue-green/10 text-vue-green px-1.5 py-0.5 rounded font-medium">
              {{ lead.product_name }}
            </span>
            <span v-if="lead.tier_classification" class="text-xs bg-surface-charcoal text-slate-text px-1.5 py-0.5 rounded">
              {{ lead.tier_classification }}
            </span>
            <span v-if="lead.category_name" class="text-xs bg-indigo-500/10 text-indigo-400 px-1.5 py-0.5 rounded font-medium">
              {{ lead.category_name }}
            </span>
          </div>

          <div v-if="lead.email" class="text-xs text-slate-text truncate">
            {{ lead.email }}
          </div>

          <div class="flex items-center justify-between text-xs text-slate-text">
            <span v-if="lead.last_contact_date">
              {{ new Date(lead.last_contact_date).toLocaleDateString() }}
            </span>
            <span v-if="getTemplateName(lead)" class="truncate max-w-[120px]">
              {{ getTemplateName(lead) }}
            </span>
          </div>

          <div class="flex items-center justify-end gap-1 pt-2 border-t border-outline-variant/10">
            <button
              @click.stop="emit('notes', lead)"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition cursor-pointer"
              title="Notes"
            >
              <FileText class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('sendWhatsApp', lead)"
              :disabled="!lead.selected_template_id"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
              title="Send WhatsApp"
            >
              <Smartphone class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('contact', lead)"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition cursor-pointer"
              title="Contact modal"
            >
              <MessageSquare class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('edit', lead)"
              class="p-1 text-slate-text hover:text-vue-green rounded hover:bg-vue-green/10 transition cursor-pointer"
              title="Edit"
            >
              <Pencil class="w-3.5 h-3.5" />
            </button>
            <button
              @click.stop="emit('delete', lead)"
              class="p-1 text-slate-text hover:text-error rounded hover:bg-error/10 transition cursor-pointer"
              title="Delete"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <div
          v-if="leadsByStatus(col.key).length === 0"
          class="flex items-center justify-center py-6 text-xs text-slate-text/50 border-2 border-dashed border-outline-variant/10 rounded-lg"
        >
          No leads in this column
        </div>
      </div>
    </div>
  </div>
</template>
