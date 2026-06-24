<script setup>
import { Users, Clock, ThumbsUp, CheckCircle } from '@lucide/vue'

const props = defineProps({
  metrics: { type: Object, required: true },
})

const cards = [
  { label: 'Total Leads', value: props.metrics.total_leads, icon: Users, accent: 'vue-green' },
  { label: 'Pending', value: props.metrics.pending, icon: Clock, accent: 'warning' },
  { label: 'Interested', value: props.metrics.interested, icon: ThumbsUp, accent: 'info' },
  { label: 'Clients', value: props.metrics.clients, icon: CheckCircle, accent: 'success' },
]
</script>

<template>
  <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <div
      v-for="card in cards"
      :key="card.label"
      class="glass-panel rounded-xl p-5 flex items-center gap-4 transition-all duration-300 hover:shadow-lg hover:shadow-vue-green/5"
    >
      <div
        class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-surface-container-highest flex items-center justify-center shrink-0"
        :class="{ 'text-vue-green': card.accent === 'vue-green', 'text-warning': card.accent === 'warning', 'text-info': card.accent === 'info', 'text-success': card.accent === 'success' }"
      >
        <component :is="card.icon" class="w-6 h-6" />
      </div>
      <div>
        <p class="text-sm text-slate-text">{{ card.label }}</p>
        <p class="text-2xl font-bold text-on-surface metric-glow font-display">{{ card.value }}</p>
      </div>
    </div>
  </div>
</template>
