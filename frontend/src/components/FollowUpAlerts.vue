<script setup>
import { Bell } from '@lucide/vue'

defineProps({
  alerts: { type: Array, default: () => [] },
})

const emit = defineEmits(['viewLead'])
</script>

<template>
  <div class="glass-panel rounded-xl overflow-hidden">
    <div class="flex items-center gap-2 px-5 py-4 border-b border-outline-variant/20">
      <Bell class="w-5 h-5 text-warning" />
      <h2 class="font-semibold text-on-surface">Follow-up Alerts</h2>
      <span
        v-if="alerts.length > 0"
        class="ml-auto bg-warning/10 text-warning text-xs font-medium px-2 py-0.5 rounded-full"
      >
        {{ alerts.length }} pending
      </span>
    </div>

    <div v-if="alerts.length === 0" class="p-8 text-center text-slate-text text-sm">
      No follow-up alerts. All leads are up to date.
    </div>

    <div v-else class="divide-y divide-outline-variant/10">
      <div
        v-for="alert in alerts"
        :key="alert.id"
        class="px-5 py-4 flex items-center justify-between hover:bg-surface-charcoal/50 transition"
      >
        <div>
          <p class="font-medium text-on-surface">{{ alert.store_name }}</p>
          <p class="text-sm text-slate-text">
            Last contact: {{ new Date(alert.last_contact_date).toLocaleDateString() }}
            &middot; Attempts: {{ alert.contact_attempts }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs bg-warning/10 text-warning px-2 py-0.5 rounded-full font-medium">
            2+ days
          </span>
          <button
            @click="emit('viewLead', alert.id)"
            class="text-sm text-vue-green hover:text-secondary font-medium cursor-pointer transition"
          >
            View Lead
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
