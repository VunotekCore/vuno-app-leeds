<script setup>
import { Users, Smartphone, Pencil, Trash2, Loader, RotateCcw } from '@lucide/vue'

defineProps({
  leads: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  templates: { type: Array, default: () => [] },
  products: { type: Array, default: () => [] },
  statusScope: { type: String, default: 'prospecting' },
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

    <template v-else>
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-surface-container text-slate-text text-xs uppercase tracking-wider">
            <th class="text-left px-4 py-3 font-medium">Store</th>
            <th class="text-left px-4 py-3 font-medium">Contact</th>
            <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Product</th>
            <th class="text-left px-4 py-3 font-medium">Status</th>
            <th class="text-left px-4 py-3 font-medium hidden lg:table-cell">Tier</th>
            <th class="text-left px-4 py-3 font-medium hidden lg:table-cell">Template</th>
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
            <td class="px-4 py-3 text-on-surface-variant text-sm hidden md:table-cell">
              <span v-if="lead.product_name" class="inline-flex items-center gap-1">
                {{ lead.product_name }}
              </span>
              <span v-else class="text-slate-text">-</span>
            </td>
            <td class="px-4 py-3">
              <template v-if="statusScope === 'archived'">
                <span class="text-sm text-slate-text">{{ lead.contact_status }}</span>
              </template>
              <template v-else-if="statusScope === 'clients'">
                <select
                  :value="lead.contact_status"
                  @change="emit('updateStatus', { ...lead, contact_status: $event.target.value })"
                  class="text-xs bg-surface-charcoal border border-outline-variant/30 rounded px-2 py-1 text-on-surface focus:ring-2 focus:ring-vue-green/40 outline-none cursor-pointer"
                >
                  <option value="Client">Client</option>
                  <option value="Archived">Archived</option>
                </select>
              </template>
              <template v-else>
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
                </select>
              </template>
              <div v-if="lead.last_contact_date" class="text-xs text-slate-text mt-0.5">
                {{ new Date(lead.last_contact_date).toLocaleDateString() }}
              </div>
            </td>
            <td class="px-4 py-3 text-on-surface-variant hidden lg:table-cell">
              {{ lead.tier_classification || '-' }}
            </td>
            <td class="px-4 py-3 hidden lg:table-cell">
              <select
                v-model="lead.selected_template_id"
                class="text-xs bg-surface-charcoal border border-outline-variant/30 rounded px-2 py-1 text-on-surface focus:ring-2 focus:ring-vue-green/40 outline-none"
              >
                <option :value="null">No template</option>
                <option
                  v-for="tpl in templates.filter(t => !t.product_id || t.product_id === lead.product_id)"
                  :key="tpl.id"
                  :value="tpl.id"
                >
                  {{ tpl.template_name }}<template v-if="tpl.product_name"> ({{ tpl.product_name }})</template>
                </option>
              </select>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <template v-if="statusScope === 'archived'">
                  <button
                    @click="emit('updateStatus', { ...lead, contact_status: 'Pending' })"
                    class="flex items-center gap-1 px-2.5 py-1.5 bg-surface-charcoal hover:bg-vue-green/10 text-slate-text hover:text-vue-green text-xs font-semibold rounded-lg transition cursor-pointer"
                    title="Reactivate lead"
                  >
                    <RotateCcw class="w-3.5 h-3.5" />
                    Reactivar
                  </button>
                </template>
                <template v-else>
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
                </template>
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

    <!-- Mobile/tablet: cards -->
    <div class="lg:hidden grid gap-3">
      <div
        v-for="lead in leads"
        :key="lead.id"
        class="glass-panel rounded-xl p-4 transition hover:border-vue-green/30"
      >
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 mb-2">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-on-surface truncate">{{ lead.store_name }}</p>
            <p class="text-xs text-slate-text truncate">{{ lead.phone ? `+${lead.phone}` : '-' }}</p>
            <p v-if="lead.email" class="text-xs text-slate-text truncate">{{ lead.email }}</p>
          </div>
          <div class="flex gap-1 shrink-0">
            <button
              @click="emit('edit', lead)"
              class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
              title="Edit"
            >
              <Pencil class="w-4 h-4" />
            </button>
            <button
              @click="emit('delete', lead)"
              class="p-1.5 text-slate-text hover:text-error rounded-lg hover:bg-error/10 transition cursor-pointer"
              title="Delete"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="flex flex-wrap gap-1.5 mb-3">
          <span
            v-if="lead.product_name"
            class="text-xs bg-vue-green/10 text-vue-green px-1.5 py-0.5 rounded font-medium"
          >
            {{ lead.product_name }}
          </span>
          <span
            v-if="lead.tier_classification"
            class="text-xs bg-surface-charcoal text-slate-text px-1.5 py-0.5 rounded"
          >
            {{ lead.tier_classification }}
          </span>
          <span v-if="lead.last_contact_date" class="text-xs text-slate-text px-1.5 py-0.5">
            {{ new Date(lead.last_contact_date).toLocaleDateString() }}
          </span>
        </div>

        <!-- Template select (clients) or archived status -->
        <div v-if="statusScope === 'archived'" class="text-xs text-slate-text mb-3">
          Status: {{ lead.contact_status }}
        </div>
        <div v-else class="mb-3">
          <select
            v-model="lead.selected_template_id"
            class="w-full text-xs bg-surface-charcoal border border-outline-variant/30 rounded-lg px-3 py-2 text-on-surface focus:ring-2 focus:ring-vue-green/40 outline-none"
          >
            <option :value="null">No template</option>
            <option
              v-for="tpl in templates.filter(t => !t.product_id || t.product_id === lead.product_id)"
              :key="tpl.id"
              :value="tpl.id"
            >
              {{ tpl.template_name }}<template v-if="tpl.product_name"> ({{ tpl.product_name }})</template>
            </option>
          </select>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-2 pt-3 border-t border-outline-variant/10">
          <template v-if="statusScope === 'archived'">
            <button
              @click="emit('updateStatus', { ...lead, contact_status: 'Pending' })"
              class="flex items-center gap-1 px-3 py-1.5 bg-surface-charcoal hover:bg-vue-green/10 text-slate-text hover:text-vue-green text-xs font-semibold rounded-lg transition cursor-pointer"
            >
              <RotateCcw class="w-3.5 h-3.5" />
              Reactivar
            </button>
          </template>
          <template v-else>
            <select
              :value="lead.contact_status"
              @change="emit('updateStatus', { ...lead, contact_status: $event.target.value })"
              class="text-xs bg-surface-charcoal border border-outline-variant/30 rounded-lg px-2 py-1.5 text-on-surface focus:ring-2 focus:ring-vue-green/40 outline-none cursor-pointer"
              :class="lead.contact_status === 'Client' ? 'text-vue-green font-medium' : ''"
            >
              <option value="Client">Client</option>
              <option value="Archived">Archived</option>
            </select>
            <button
              @click="emit('sendWhatsApp', lead)"
              :disabled="!lead.selected_template_id"
              class="flex items-center gap-1 px-3 py-1.5 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep text-xs font-semibold rounded-lg transition cursor-pointer"
            >
              <Smartphone class="w-3.5 h-3.5" />
              WA
            </button>
            <button
              @click="emit('contact', lead)"
              class="p-1.5 text-slate-text hover:text-vue-green rounded-lg hover:bg-vue-green/10 transition cursor-pointer"
              title="Contact via modal"
            >
              <Smartphone class="w-4 h-4" />
            </button>
          </template>
        </div>
      </div>
    </div>
    </template>
  </div>
</template>
