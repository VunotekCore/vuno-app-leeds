<script setup>
import { ref, computed, watch } from 'vue'
import api from '../services/api'
import { X, Check, XCircle, Loader } from '@lucide/vue'

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
          <div class="flex justify-between gap-4">
            <span class="text-slate-text shrink-0">Phone</span>
            <span class="text-on-surface font-medium truncate">+{{ lead.phone }}</span>
          </div>
          <div v-if="lead.email" class="flex justify-between gap-4">
            <span class="text-slate-text shrink-0">Email</span>
            <span class="text-on-surface font-medium truncate">{{ lead.email }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-text shrink-0">Status</span>
            <span class="text-on-surface font-medium">{{ lead.contact_status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-text shrink-0">Tier</span>
            <span class="text-on-surface font-medium">{{ lead.tier_classification || '-' }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-slate-text shrink-0">Product</span>
            <span class="text-on-surface font-medium truncate">{{ lead.product_name || '-' }}</span>
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

        <div class="flex items-center justify-between pt-2">
          <button
            @click="emit('close')"
            class="p-2 text-slate-text hover:text-on-surface rounded-lg hover:bg-surface-charcoal transition cursor-pointer"
            title="Close"
          >
            <X class="w-5 h-5" />
          </button>
          <button
            v-if="!result?.success"
            @click="handleSend"
            :disabled="!selectedTemplateId || sending"
            class="flex items-center justify-center p-2.5 bg-[#25D366] hover:bg-[#128C7E] disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition cursor-pointer"
            title="Send via WhatsApp"
          >
            <Loader v-if="sending" class="w-4 h-4 animate-spin text-white" />
            <svg v-else viewBox="0 0 24 24" class="w-4 h-4 fill-white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
