<script setup>
import { computed } from 'vue'
import {
  Clock, Phone, PhoneCall, ThumbsUp, CheckCircle, Archive,
  DollarSign, TrendingUp,
} from '@lucide/vue'

const props = defineProps({
  projection: { type: Object, required: true },
})

const statusConfig = {
  Pending:        { label: 'Pending',        icon: Clock,      color: 'text-warning',    bar: 'bg-warning/60' },
  'First Contact': { label: 'First Contact',  icon: Phone,      color: 'text-info',       bar: 'bg-info/60' },
  'Second Contact':{ label: 'Second Contact', icon: PhoneCall,  color: 'text-cyan-400',   bar: 'bg-cyan-400/60' },
  Interested:     { label: 'Interested',     icon: ThumbsUp,    color: 'text-vue-green',  bar: 'bg-vue-green/60' },
  Client:         { label: 'Client',         icon: CheckCircle, color: 'text-success',    bar: 'bg-success/60' },
  Archived:       { label: 'Archived',       icon: Archive,     color: 'text-slate-text', bar: 'bg-slate-text/20' },
}

const orderedStatuses = ['Pending', 'First Contact', 'Second Contact', 'Interested', 'Client', 'Archived']

const rows = computed(() => {
  const breakdownMap = {}
  for (const item of (props.projection?.breakdown ?? [])) {
    breakdownMap[item.contact_status] = item
  }
  return orderedStatuses.map(status => {
    const data = breakdownMap[status]
    const count = data ? parseInt(data.count) : 0
    const value = data ? parseFloat(data.total_value) : 0
    const config = statusConfig[status] || {}
    return { status, count, value, ...config }
  })
})

const grandTotal = computed(() => {
  return parseFloat(props.projection?.grand_total ?? 0)
})

const maxValue = computed(() => {
  return Math.max(...rows.value.map(r => r.value), 1)
})

function formatUSD(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}
</script>

<template>
  <div class="glass-panel rounded-xl overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-4 border-b border-outline-variant/20">
      <TrendingUp class="w-5 h-5 text-vue-green" />
      <h2 class="font-semibold text-on-surface font-display">Pipeline Revenue</h2>
      <div class="ml-auto flex items-center gap-2">
        <DollarSign class="w-4 h-4 text-vue-green" />
        <span class="text-lg font-bold text-vue-green metric-glow font-display">
          {{ formatUSD(grandTotal) }}
        </span>
      </div>
    </div>

    <div v-if="grandTotal === 0" class="p-8 text-center text-slate-text text-sm">
      No projection data available. Assign tiers to leads to see revenue projections.
    </div>

    <div v-else class="p-5 space-y-3">
      <div
        v-for="row in rows"
        :key="row.status"
        class="flex items-center gap-2 sm:gap-3"
      >
        <component
          :is="row.icon"
          :class="[row.color, 'w-4 h-4 shrink-0']"
        />
        <span class="text-sm text-on-surface w-20 sm:w-28 min-w-0 truncate">{{ row.label }}</span>
        <span class="text-xs text-slate-text w-12 sm:w-16 shrink-0 text-right whitespace-nowrap">{{ row.count }}</span>
        <div class="flex-1 h-2 rounded-full bg-surface-container-highest overflow-hidden min-w-[12px]">
          <div
            :class="[row.bar, 'h-full rounded-full transition-all duration-500']"
            :style="{ width: (row.value / maxValue * 100) + '%' }"
          />
        </div>
        <span class="text-xs sm:text-sm font-semibold text-on-surface w-20 sm:w-24 text-right shrink-0 font-display">
          {{ formatUSD(row.value) }}
        </span>
      </div>

      <div class="flex items-center gap-2 sm:gap-3 pt-3 border-t border-outline-variant/20">
        <TrendingUp class="w-4 h-4 text-vue-green shrink-0" />
        <span class="text-sm font-bold text-on-surface w-20 sm:w-28 min-w-0 truncate font-display">TOTAL</span>
        <span class="text-xs text-slate-text w-12 sm:w-16 shrink-0 text-right whitespace-nowrap">{{ rows.reduce((a, r) => a + r.count, 0) }}</span>
        <div class="flex-1 h-2 rounded-full bg-surface-container-highest overflow-hidden min-w-[12px]">
          <div class="h-full rounded-full bg-gradient-to-r from-vue-green to-success" style="width: 100%" />
        </div>
        <span class="text-xs sm:text-sm font-bold text-vue-green w-20 sm:w-24 text-right shrink-0 metric-glow font-display">
          {{ formatUSD(grandTotal) }}
        </span>
      </div>
    </div>
  </div>
</template>
