<script setup>
import { ref, computed, watch } from 'vue'
import api from '../services/api'
import { sanitizePhone, formatPhone } from '../utils/phone'
import { X, AlertTriangle, Check } from '@lucide/vue'

const props = defineProps({
  tierMapping: { type: Object, default: () => ({}) },
  products: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  lead: { type: Object, default: null },
})

const emit = defineEmits(['close', 'created', 'updated'])

const isEditing = computed(() => !!props.lead)

const form = ref({
  store_name: '',
  profile_url: '',
  phone: '',
  email: '',
  followers_count: 0,
  tier_classification: '',
  product_id: null,
  category_id: null,
  contact_status: 'Pending',
})

if (props.lead) {
  form.value = {
    store_name: props.lead.store_name || '',
    profile_url: props.lead.profile_url || '',
    phone: props.lead.phone || '',
    email: props.lead.email || '',
    followers_count: props.lead.followers_count || 0,
    tier_classification: props.lead.tier_classification || '',
    product_id: props.lead.product_id || null,
    category_id: props.lead.category_id || null,
    contact_status: props.lead.contact_status || 'Pending',
  }
}

const errors = ref({})
const submitting = ref(false)
const duplicateCheck = ref({ checking: false, exists: false, lead: null })
const serverError = ref('')

const sanitizedPhone = computed(() => {
  if (!form.value.phone) return ''
  return sanitizePhone(form.value.phone)
})

const formattedPhone = computed(() => {
  if (!sanitizedPhone.value) return ''
  return formatPhone(form.value.phone)
})

const isDuplicate = computed(() => duplicateCheck.value.exists)

let debounceTimer = null

watch(
  () => form.value.phone,
  () => {
    clearTimeout(debounceTimer)
    if (isEditing.value) return
    if (!form.value.phone || form.value.phone.length < 6) {
      duplicateCheck.value = { checking: false, exists: false, lead: null }
      return
    }
    duplicateCheck.value.checking = true
    debounceTimer = setTimeout(checkDuplicate, 400)
  }
)

watch(
  () => form.value.profile_url,
  () => {
    clearTimeout(debounceTimer)
    if (isEditing.value) return
    if (!form.value.profile_url) {
      duplicateCheck.value = { checking: false, exists: false, lead: null }
      return
    }
    duplicateCheck.value.checking = true
    debounceTimer = setTimeout(checkDuplicate, 400)
  }
)

async function checkDuplicate() {
  if (isEditing.value) return
  try {
    const { data } = await api.get('/api/leads/check-duplicate', {
      params: {
        phone: sanitizePhone(form.value.phone),
        profile_url: form.value.profile_url,
      },
    })
    duplicateCheck.value = {
      checking: false,
      exists: data.data.exists,
      lead: data.data.lead,
    }
  } catch {
    duplicateCheck.value.checking = false
  }
}

function validate() {
  const errs = {}
  if (!form.value.store_name.trim()) errs.store_name = 'Store name is required'
  if (!form.value.profile_url.trim()) errs.profile_url = 'Profile URL is required'
  if (!form.value.phone.trim()) errs.phone = 'Phone is required'
  else if (sanitizedPhone.value.length < 10) errs.phone = 'Phone must have at least 10 digits'
  errors.value = errs
  return Object.keys(errs).length === 0
}

async function handleSubmit() {
  if (!validate()) return
  if (!isEditing.value && isDuplicate.value) return
  submitting.value = true
  serverError.value = ''

  try {
    const payload = {
      ...form.value,
      phone: sanitizedPhone.value,
    }

    if (isEditing.value) {
      await api.put(`/api/leads/${props.lead.id}`, payload)
      emit('updated')
    } else {
      await api.post('/api/leads', payload)
      emit('created')
    }
  } catch (err) {
    if (err.response?.status === 409) {
      duplicateCheck.value.exists = true
      duplicateCheck.value.lead = err.response.data.data?.existing_lead || null
    } else {
      serverError.value = err.response?.data?.message || 'Failed to save lead'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-start justify-center pt-12 pb-12 bg-black/60 overflow-y-auto backdrop-blur-sm">
    <div class="glass-panel rounded-xl shadow-xl w-full max-w-3xl mx-4">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/20">
        <h2 class="text-lg font-bold text-on-surface font-display">
          {{ isEditing ? 'Edit Lead' : 'Register New Lead' }}
        </h2>
        <button @click="emit('close')" class="text-slate-text hover:text-on-surface transition cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Store Name *</label>
            <input
              v-model="form.store_name"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="e.g., Fashion Store"
            />
            <p v-if="errors.store_name" class="text-xs text-error mt-1">{{ errors.store_name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Profile URL *</label>
            <input
              v-model="form.profile_url"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="https://instagram.com/store"
            />
            <p v-if="errors.profile_url" class="text-xs text-error mt-1">{{ errors.profile_url }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Phone *</label>
            <input
              v-model="form.phone"
              type="tel"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="+505 8888 8888"
            />
            <div class="flex items-center gap-2 mt-1">
              <Check v-if="sanitizedPhone && sanitizedPhone.length >= 10" class="w-3 h-3 text-success" />
              <span
                v-if="sanitizedPhone"
                class="text-xs"
                :class="sanitizedPhone.length >= 10 ? 'text-success' : 'text-slate-text'"
              >
                Sanitized: {{ sanitizedPhone }} {{ formattedPhone ? `(${formattedPhone})` : '' }}
              </span>
            </div>
            <p v-if="errors.phone" class="text-xs text-error mt-1">{{ errors.phone }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
              placeholder="store@email.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Product</label>
            <select
              v-model="form.product_id"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
            >
              <option :value="null">No product</option>
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Category</label>
            <select
              v-model="form.category_id"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
            >
              <option :value="null">No category</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
          </div>

          <div
            v-if="duplicateCheck.exists"
            class="col-span-full flex items-start gap-2 bg-error-container/20 border border-error/30 rounded-lg px-3 py-2"
          >
            <AlertTriangle class="w-4 h-4 text-error mt-0.5 shrink-0" />
            <div class="text-xs text-error">
              Duplicate: <strong>{{ duplicateCheck.lead?.store_name }}</strong> already registered with this phone/URL.
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Status</label>
            <select
              v-model="form.contact_status"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
            >
              <option value="Pending">Pending</option>
              <option value="First Contact">First Contact</option>
              <option value="Second Contact">Second Contact</option>
              <option value="Interested">Interested</option>
              <option value="Client">Client</option>
              <option value="Archived">Archived</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Followers</label>
            <input
              v-model="form.followers_count"
              type="number"
              min="0"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-on-surface-variant mb-1">Tier</label>
            <select
              v-model="form.tier_classification"
              class="w-full px-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition"
            >
              <option value="">Select tier</option>
              <option v-for="(price, tier) in tierMapping" :key="tier" :value="tier">
                {{ tier }} ({{ price }})
              </option>
            </select>
          </div>
        </div>

        <div v-if="serverError" class="bg-error-container/20 border border-error/30 text-error px-4 py-2.5 rounded-lg text-sm">
          {{ serverError }}
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 text-sm text-slate-text hover:text-on-surface transition cursor-pointer"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting || isDuplicate || duplicateCheck.checking"
            class="px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
          >
            <span v-if="submitting" class="flex items-center gap-2">
              <div class="w-3 h-3 border-2 border-forest-deep/30 border-t-forest-deep rounded-full animate-spin" />
              Saving...
            </span>
            <span v-else>{{ isEditing ? 'Update Lead' : 'Register Lead' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
