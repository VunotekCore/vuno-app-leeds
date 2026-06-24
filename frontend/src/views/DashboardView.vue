<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import DashboardCards from '../components/DashboardCards.vue'
import FollowUpAlerts from '../components/FollowUpAlerts.vue'
import { BarChart3, Loader } from '@lucide/vue'

const router = useRouter()
const metrics = ref(null)
const followUpAlerts = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get('/api/dashboard')
    metrics.value = data.data.metrics
    followUpAlerts.value = data.data.follow_up_alerts
  } catch {
    //
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center gap-3">
      <BarChart3 class="w-6 h-6 text-vue-green" />
      <h1 class="text-xl font-bold text-on-surface font-display">Dashboard</h1>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <Loader class="w-8 h-8 text-vue-green animate-spin" />
    </div>

    <template v-else>
      <DashboardCards v-if="metrics" :metrics="metrics" />
      <FollowUpAlerts
        :alerts="followUpAlerts"
        @view-lead="() => router.push({ name: 'Leads' })"
      />
    </template>
  </div>
</template>
