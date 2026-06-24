<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'
import LeadForm from '../components/LeadForm.vue'
import LeadTable from '../components/LeadTable.vue'
import LeadContactModal from '../components/LeadContactModal.vue'
import { Plus, Search, Users } from '@lucide/vue'

const leads = ref([])
const total = ref(0)
const page = ref(1)
const perPage = 50
const search = ref('')
const statusFilter = ref('')
const loading = ref(true)
const showForm = ref(false)
const editingLead = ref(null)
const contactingLead = ref(null)
const templates = ref([])
const tiers = ref([])

const tierMapping = computed(() => {
  const map = {}
  for (const t of tiers.value) {
    map[t.name] = `$${parseFloat(t.price).toFixed(2)} USD`
  }
  return map
})

async function fetchLeads() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage }
    if (search.value) params.search = search.value
    if (statusFilter.value) params.contact_status = statusFilter.value

    const { data } = await api.get('/api/leads', { params })
    leads.value = data.data
    total.value = data.meta.total
  } catch {
    //
  } finally {
    loading.value = false
  }
}

async function fetchTemplates() {
  try {
    const { data } = await api.get('/api/templates')
    templates.value = data.data
  } catch {
    //
  }
}

async function fetchTiers() {
  try {
    const { data } = await api.get('/api/tiers')
    tiers.value = data.data
  } catch {
    //
  }
}

async function handleDelete(lead) {
  if (!confirm(`Delete lead "${lead.store_name}"?`)) return
  try {
    await api.delete(`/api/leads/${lead.id}`)
    await fetchLeads()
  } catch {
    //
  }
}

async function handleUpdateStatus(lead) {
  try {
    await api.put(`/api/leads/${lead.id}`, {
      contact_status: lead.contact_status,
    })
    await fetchLeads()
  } catch {
    //
  }
}

async function handleSendWhatsApp(lead) {
  try {
    const { data } = await api.post(`/api/leads/${lead.id}/send`, {
      template_id: lead.selected_template_id,
    })
    if (data.data.wa_url) {
      window.open(data.data.wa_url, '_blank', 'noopener,noreferrer')
    }
    await fetchLeads()
  } catch {
    //
  }
}

function handleLeadCreated() {
  showForm.value = false
  editingLead.value = null
  fetchLeads()
}

function handleLeadUpdated() {
  showForm.value = false
  editingLead.value = null
  fetchLeads()
}

function handleEdit(lead) {
  editingLead.value = lead
  showForm.value = true
}

function handleNewLead() {
  editingLead.value = null
  showForm.value = true
}

function handleContact(lead) {
  contactingLead.value = lead
}

function handleContactSent() {
  contactingLead.value = null
  fetchLeads()
}

function handleSearch() {
  page.value = 1
  fetchLeads()
}

onMounted(() => {
  fetchLeads()
  fetchTemplates()
  fetchTiers()
})
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Users class="w-6 h-6 text-vue-green" />
        <h1 class="text-xl font-bold text-on-surface font-display">Leads</h1>
      </div>
      <button
        @click="handleNewLead"
        class="flex items-center gap-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold px-4 py-2 rounded-lg text-sm transition cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        New Lead
      </button>
    </div>

    <div class="flex gap-3">
      <div class="relative flex-1 max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-text" />
        <input
          v-model="search"
          @input="handleSearch"
          placeholder="Search leads..."
          class="w-full pl-10 pr-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none text-sm transition"
        />
      </div>
      <select
        v-model="statusFilter"
        @change="fetchLeads"
        class="bg-surface-charcoal border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface focus:ring-2 focus:ring-vue-green/40 outline-none transition"
      >
        <option value="">All statuses</option>
        <option value="Pending">Pending</option>
        <option value="First Contact">First Contact</option>
        <option value="Second Contact">Second Contact</option>
        <option value="Interested">Interested</option>
        <option value="Client">Client</option>
        <option value="Archived">Archived</option>
      </select>
    </div>

    <LeadForm
      v-if="showForm"
      :tier-mapping="tierMapping"
      :lead="editingLead"
      @close="showForm = false; editingLead = null"
      @created="handleLeadCreated"
      @updated="handleLeadUpdated"
    />

    <LeadContactModal
      v-if="contactingLead"
      :lead="contactingLead"
      :templates="templates"
      :tier-mapping="tierMapping"
      @close="contactingLead = null"
      @sent="handleContactSent"
    />

    <LeadTable
      :leads="leads"
      :loading="loading"
      :templates="templates"
      @contact="handleContact"
      @send-whats-app="handleSendWhatsApp"
      @update-status="handleUpdateStatus"
      @edit="handleEdit"
      @delete="handleDelete"
    />
  </div>
</template>
