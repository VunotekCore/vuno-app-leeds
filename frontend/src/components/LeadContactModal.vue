<script setup>
import { ref, computed, watch } from 'vue'
import api from '../services/api'
import { X, Smartphone, Check, XCircle, Loader } from '@lucide/vue'

const props = defineProps({
  lead: { type: Object, required: true },
  templates: { type: Array, default: () => [] },
  tierMapping: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['close', 'sent'])

const selectedTemplateId = ref(null)
const sending = ref(false)
const previewMessage = ref('')
const result = ref(null) // { success: true/false, message: '' }

const tierPrice = computed(() => props.tierMapping[props.lead.tier_classification] || '')

const filteredTemplates = computed(() => {
  if (!props.lead.product_id) {
    return props.templates.filter((t) => !t.product_id)
  }
  return props.templates.filter(
    (t) => !t.product_id || t.product_id === props.lead.product_id
  )
})

watch(selectedTemplateId, (id) => {
  result.value = null
  if (!id) {
    previewMessage.value = ''
    return
  }
  const tpl = props.templates.find((t) => t.id === id)
  if (!tpl) {
    previewMessage.value = ''
    return
  }
  previewMessage.value = tpl.message_body
    .replace(/\[StoreName\]/g, props.lead.store_name)
    .replace(/\[TierPrice\]/g, tierPrice.value)
    .replace(/\[ProductName\]/g, props.lead.product_name || '')
})

async function handleSend() {
  if (!selectedTemplateId.value) return
  sending.value = true
  result.value = null
  try {
    const { data } = await api.post(`/api/leads/${props.lead.id}/send`, {
      template_id: selectedTemplateId.value,
    })
    if (data.data.wa_url) {
      window.open(data.data.wa_url, '_blank', 'noopener,noreferrer')
    }
    result.value = { success: true, message: 'Message sent successfully via WhatsApp' }
    emit('sent', data.data)
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to send message'
    result.value = { success: false, message: msg }
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="glass-panel rounded-xl shadow-xl w-full max-w-md mx-4">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/20">
        <div>
          <h2 class="text-lg font-bold text-on-surface font-display">Contact Lead</h2>
          <p class="text-sm text-slate-text mt-0.5">{{ lead.store_name }}</p>
        </div>
        <button @click="emit('close')" class="text-slate-text hover:text-on-surface transition cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="p-6 space-y-4">
        <div class="glass-panel rounded-lg p-3 space-y-1 text-sm">
          <div class="flex justify-between">
            <span class="text-slate-text">Phone</span>
            <span class="text-on-surface font-medium">+{{ lead.phone }}</span>
          </div>
          <div v-if="lead.email" class="flex justify-between">
            <span class="text-slate-text">Email</span>
            <span class="text-on-surface font-medium">{{ lead.email }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-text">Status</span>
            <span class="text-on-surface font-medium">{{ lead.contact_status }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-text">Tier</span>
            <span class="text-on-surface font-medium">{{ lead.tier_classification || '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-text">Product</span>
            <span class="text-on-surface font-medium">{{ lead.product_name || '-' }}</span>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-on-surface-variant mb-1">Template</label>
          <select
            v-model="selectedTemplateId"
            class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
          >
            <option :value="null">Select a template...</option>
            <option v-for="tpl in filteredTemplates" :key="tpl.id" :value="tpl.id">
              {{ tpl.template_name }}<template v-if="tpl.product_name"> ({{ tpl.product_name }})</template>
            </option>
          </select>
        </div>

        <div v-if="previewMessage">
          <label class="block text-sm font-medium text-on-surface-variant mb-1">Preview</label>
          <div class="bg-surface-charcoal border border-outline-variant/30 rounded-lg p-3 text-sm text-on-surface-variant whitespace-pre-wrap max-h-32 overflow-y-auto">
            {{ previewMessage }}
          </div>
        </div>

        <div
          v-if="result"
          class="flex items-start gap-2 px-4 py-3 rounded-lg text-sm"
          :class="result.success
            ? 'bg-success/10 text-success border border-success/20'
            : 'bg-error/10 text-error border border-error/20'"
        >
          <Check v-if="result.success" class="w-4 h-4 mt-0.5 shrink-0" />
          <XCircle v-else class="w-4 h-4 mt-0.5 shrink-0" />
          <span>{{ result.message }}</span>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            @click="emit('close')"
            class="px-4 py-2 text-sm text-slate-text hover:text-on-surface transition cursor-pointer"
          >
            Close
          </button>
          <button
            v-if="!result?.success"
            @click="handleSend"
            :disabled="!selectedTemplateId || sending"
            class="flex items-center gap-2 px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
          >
            <Loader v-if="sending" class="w-4 h-4 animate-spin" />
            <Smartphone v-else class="w-4 h-4" />
            {{ sending ? 'Sending...' : 'Send via WhatsApp' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
