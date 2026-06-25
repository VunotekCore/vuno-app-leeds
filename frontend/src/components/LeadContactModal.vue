<script setup>
import { ref, computed, watch } from 'vue'
import api from '../services/api'
import { X, Check, XCircle, Loader, Smartphone, Mail } from '@lucide/vue'

const props = defineProps({
  lead: { type: Object, required: true },
  templates: { type: Array, default: () => [] },
  tierMapping: { type: Object, default: () => ({}) },
  channel: { type: String, default: 'whatsapp' },
})

const emit = defineEmits(['close', 'sent'])

const selectedTemplateId = ref(props.lead.selected_template_id || null)
const selectedChannel = ref(props.channel)
const subject = ref('')
const subjectDirty = ref(false)
const sending = ref(false)
const previewMessage = ref('')
const result = ref(null)

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

  if (selectedChannel.value === 'email' && !subjectDirty.value) {
    subject.value = tpl.template_name
  }
})

watch(selectedChannel, (ch) => {
  result.value = null
  if (ch === 'email' && selectedTemplateId.value && !subjectDirty.value) {
    const tpl = props.templates.find((t) => t.id === selectedTemplateId.value)
    if (tpl) subject.value = tpl.template_name
  }
})

async function handleSend() {
  if (!selectedTemplateId.value) return
  if (selectedChannel.value === 'email' && !props.lead.email) return
  if (selectedChannel.value === 'email' && !subject.value.trim()) return

  sending.value = true
  result.value = null
  try {
    const { data } = await api.post(`/api/leads/${props.lead.id}/send`, {
      template_id: selectedTemplateId.value,
      channel: selectedChannel.value,
      subject: selectedChannel.value === 'email' ? subject.value.trim() : undefined,
    })
    if (selectedChannel.value === 'whatsapp' && data.data.wa_url) {
      window.open(data.data.wa_url, '_blank', 'noopener,noreferrer')
    }
    const channelLabel = selectedChannel.value === 'whatsapp' ? 'WhatsApp' : 'Email'
    result.value = { success: true, message: `Message sent successfully via ${channelLabel}` }
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

      <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
        <!-- Channel selector -->
        <div class="flex gap-2">
          <button
            @click="selectedChannel = 'whatsapp'"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition cursor-pointer"
            :class="selectedChannel === 'whatsapp'
              ? 'bg-[#25D366]/20 text-[#25D366] border border-[#25D366]/30'
              : 'bg-surface-charcoal text-slate-text border border-outline-variant/20 hover:border-outline'"
          >
            <Smartphone class="w-4 h-4" />
            WhatsApp
          </button>
          <button
            @click="selectedChannel = 'email'"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition cursor-pointer"
            :class="selectedChannel === 'email'
              ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30'
              : 'bg-surface-charcoal text-slate-text border border-outline-variant/20 hover:border-outline'"
          >
            <Mail class="w-4 h-4" />
            Email
          </button>
        </div>

        <!-- Lead info -->
        <div class="glass-panel rounded-lg p-3 space-y-1 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-slate-text shrink-0">Phone</span>
            <span class="text-on-surface font-medium truncate">+{{ lead.phone }}</span>
          </div>
          <div v-if="lead.email" class="flex justify-between gap-4">
            <span class="text-slate-text shrink-0">Email</span>
            <span class="text-on-surface font-medium truncate">{{ lead.email }}</span>
          </div>
          <div v-else-if="selectedChannel === 'email'" class="flex justify-between gap-4">
            <span class="text-error shrink-0">Email</span>
            <span class="text-error font-medium">No email address</span>
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

        <!-- Subject (email only) -->
        <div v-if="selectedChannel === 'email'">
          <label class="block text-sm font-medium text-on-surface-variant mb-1">Subject</label>
          <input
            v-model="subject"
            @input="subjectDirty = true"
            type="text"
            placeholder="Email subject..."
            class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
          />
        </div>

        <!-- Template select -->
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

        <!-- Preview -->
        <div v-if="previewMessage">
          <label class="block text-sm font-medium text-on-surface-variant mb-1">Preview</label>
          <div v-if="selectedChannel === 'email' && subject" class="bg-surface-charcoal border border-outline-variant/30 rounded-lg p-3 text-sm space-y-1">
            <div class="text-on-surface font-medium truncate">Subject: {{ subject }}</div>
            <div class="text-on-surface-variant whitespace-pre-wrap max-h-32 overflow-y-auto border-t border-outline-variant/20 pt-1">
              {{ previewMessage }}
            </div>
          </div>
          <div v-else class="bg-surface-charcoal border border-outline-variant/30 rounded-lg p-3 text-sm text-on-surface-variant whitespace-pre-wrap max-h-32 overflow-y-auto">
            {{ previewMessage }}
          </div>
        </div>

        <!-- Result -->
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

        <!-- Actions -->
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
            :disabled="!selectedTemplateId || sending || (selectedChannel === 'email' && (!lead.email || !subject.trim()))"
            class="flex items-center justify-center gap-2 px-5 py-2.5 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition cursor-pointer text-sm font-semibold"
            :class="selectedChannel === 'whatsapp'
              ? 'bg-[#25D366] hover:bg-[#128C7E] text-white'
              : 'bg-blue-500 hover:bg-blue-600 text-white'"
            :title="selectedChannel === 'whatsapp' ? 'Send via WhatsApp' : 'Send via Email'"
          >
            <Loader v-if="sending" class="w-4 h-4 animate-spin" />
            <template v-else>
              <Smartphone v-if="selectedChannel === 'whatsapp'" class="w-4 h-4" />
              <Mail v-else class="w-4 h-4" />
              {{ selectedChannel === 'whatsapp' ? 'Send WhatsApp' : 'Send Email' }}
            </template>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
