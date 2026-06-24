<script setup>
import { computed } from 'vue'
import {
  Smartphone, Pencil, Trash2, MessageSquare,
} from '@lucide/vue'

const props = defineProps({
  leads: { type: Array, default: () => [] },
  templates: { type: Array, default: () => [] },
  products: { type: Array, default: () => [] },
  search: { type: String, default: '' },
})

const emit = defineEmits(['updateStatus', 'edit', 'delete', 'contact', 'sendWhatsApp'])

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
    l.product_name?.toLowerCase().includes(q)
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
</script>

<template>
  <div class="flex gap-4 overflow-x-auto pb-4 min-h-[60vh]">
    <div
      v-for="col in statusColumns"
      :key="col.key"
      class="flex-1 min-w-[240px] bg-surface-charcoal/50 rounded-xl border border-outline-variant/20 flex flex-col"
      @dragover="onDragOver"
      @drop="(e) => onDrop(e, col.key)"
    >
      <div class="flex items-center justify-between px-4 py-3 border-b border-outline-variant/10">
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 rounded-full" :class="col.color.replace('border-l-', 'bg-')" />
          <h3 class="text-sm font-semibold text-on-surface">{{ col.label }}</h3>
        </div>
        <span class="text-xs text-slate-text bg-surface-charcoal px-2 py-0.5 rounded-full">
          {{ leadsByStatus(col.key).length }}
        </span>
      </div>

      <div class="flex-1 p-3 space-y-3 overflow-y-auto">
        <div
          v-for="lead in leadsByStatus(col.key)"
          :key="lead.id"
          :draggable="true"
          @dragstart="(e) => onDragStart(e, lead.id)"
          class="glass-panel rounded-lg p-2 sm:p-3 border border-outline-variant/10 cursor-grab active:cursor-grabbing hover:border-vue-green/30 transition-all duration-150 space-y-2"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-on-surface truncate">{{ lead.store_name }}</p>
              <p class="text-xs text-slate-text truncate">{{ lead.phone ? `+${lead.phone}` : '-' }}</p>
            </div>
            <div class="flex gap-1 shrink-0">
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

          <div v-if="lead.product_name || lead.tier_classification" class="flex flex-wrap gap-1">
            <span v-if="lead.product_name" class="text-xs bg-vue-green/10 text-vue-green px-1.5 py-0.5 rounded font-medium">
              {{ lead.product_name }}
            </span>
            <span v-if="lead.tier_classification" class="text-xs bg-surface-charcoal text-slate-text px-1.5 py-0.5 rounded">
              {{ lead.tier_classification }}
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
        </div>

        <div
          v-if="leadsByStatus(col.key).length === 0"
          class="flex items-center justify-center py-8 text-xs text-slate-text/50 border-2 border-dashed border-outline-variant/10 rounded-lg"
        >
          Drop leads here
        </div>
      </div>
    </div>
  </div>
</template>
