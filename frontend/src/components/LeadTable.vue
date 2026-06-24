<script setup>
import { Users, Smartphone, Pencil, Trash2, Loader } from '@lucide/vue'

defineProps({
  leads: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  templates: { type: Array, default: () => [] },
})

const emit = defineEmits(['contact', 'sendWhatsApp', 'updateStatus', 'edit', 'delete'])
</script>

<template>
  <div class="glass-panel rounded-xl overflow-hidden">
    <div v-if="loading" class="flex justify-center py-12">
      <Loader class="w-6 h-6 text-vue-green animate-spin" />
    </div>

    <div v-else-if="leads.length === 0" class="text-center py-12 text-slate-text">
      <Users class="w-12 h-12 mx-auto mb-2 opacity-50" />
      <p>No leads found</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-surface-container text-slate-text text-xs uppercase tracking-wider">
            <th class="text-left px-4 py-3 font-medium">Store</th>
            <th class="text-left px-4 py-3 font-medium">Contact</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-left px-4 py-3 font-medium">Tier</th>
            <th class="text-left px-4 py-3 font-medium">Template</th>
            <th class="text-left px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/10">
          <tr v-for="lead in leads" :key="lead.id" class="hover:bg-surface-charcoal/50 transition">
            <td class="px-4 py-3">
              <div class="font-medium text-on-surface">{{ lead.store_name }}</div>
              <div class="text-xs text-slate-text truncate max-w-[200px]">{{ lead.profile_url }}</div>
            </td>
            <td class="px-4 py-3">
              <div class="text-on-surface">{{ lead.phone ? `+${lead.phone}` : '-' }}</div>
              <div v-if="lead.email" class="text-xs text-slate-text">{{ lead.email }}</div>
            </td>
            <td class="px-4 py-3">
              <select
                :value="lead.contact_status"
                @change="emit('updateStatus', { ...lead, contact_status: $event.target.value })"
                class="text-xs bg-surface-charcoal border border-outline-variant/30 rounded px-2 py-1 text-on-surface focus:ring-2 focus:ring-vue-green/40 outline-none cursor-pointer"
              >
                <option value="Pending">Pending</option>
                <option value="First Contact">First Contact</option>
                <option value="Second Contact">Second Contact</option>
                <option value="Interested">Interested</option>
                <option value="Client">Client</option>
                <option value="Archived">Archived</option>
              </select>
              <div v-if="lead.last_contact_date" class="text-xs text-slate-text mt-0.5">
                {{ new Date(lead.last_contact_date).toLocaleDateString() }}
              </div>
            </td>
            <td class="px-4 py-3 text-on-surface-variant">
              {{ lead.tier_classification || '-' }}
            </td>
            <td class="px-4 py-3">
              <select
                v-model="lead.selected_template_id"
                class="text-xs bg-surface-charcoal border border-outline-variant/30 rounded px-2 py-1 text-on-surface focus:ring-2 focus:ring-vue-green/40 outline-none"
              >
                <option :value="null">No template</option>
                <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">
                  {{ tpl.template_name }}
                </option>
              </select>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  @click="emit('sendWhatsApp', lead)"
                  :disabled="!lead.selected_template_id"
                  class="flex items-center gap-1 px-2.5 py-1.5 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep text-xs font-semibold rounded-lg transition cursor-pointer"
                >
                  <Smartphone class="w-3.5 h-3.5" />
                  WhatsApp
                </button>
                <button
                  @click="emit('contact', lead)"
                  class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
                  title="Contact via modal"
                >
                  <Smartphone class="w-4 h-4" />
                </button>
                <button
                  @click="emit('edit', lead)"
                  class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
                >
                  <Pencil class="w-4 h-4" />
                </button>
                <button
                  @click="emit('delete', lead)"
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
</template>
