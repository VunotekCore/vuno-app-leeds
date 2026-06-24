<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../services/api'
import LeadForm from '../components/LeadForm.vue'
import LeadKanban from '../components/LeadKanban.vue'
import LeadTable from '../components/LeadTable.vue'
import LeadContactModal from '../components/LeadContactModal.vue'
import { Plus, Search, Users, Target, Star, Archive } from '@lucide/vue'

const leads = ref([])
const total = ref(0)
const page = ref(1)
const perPage = 50
const search = ref('')
const loading = ref(true)
const showForm = ref(false)
const editingLead = ref(null)
const contactingLead = ref(null)
const templates = ref([])
const tiers = ref([])
const products = ref([])

const activeTab = ref('prospecting')

const counts = reactive({
  prospecting: 0,
  clients: 0,
  archived: 0,
})

const tabFilterMap = {
  prospecting: 'Pending,First Contact,Second Contact,Interested',
  clients: 'Client',
  archived: 'Archived',
}

const statusScope = computed(() => activeTab.value)

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
    const params = { page: 1, per_page: activeTab.value === 'prospecting' ? 200 : perPage }
    if (search.value) params.search = search.value
    params.contact_status = tabFilterMap[activeTab.value]

    const { data } = await api.get('/api/leads', { params })
    leads.value = data.data
    total.value = data.meta.total
  } catch {
    //
  } finally {
    loading.value = false
    fetchCounts()
  }
}

async function fetchCounts() {
  try {
    const { data } = await api.get('/api/leads', {
      params: { per_page: 1, contact_status: tabFilterMap.prospecting },
    })
    counts.prospecting = data.meta.total
  } catch { /* */ }
  try {
    const { data } = await api.get('/api/leads', {
      params: { per_page: 1, contact_status: tabFilterMap.clients },
    })
    counts.clients = data.meta.total
  } catch { /* */ }
  try {
    const { data } = await api.get('/api/leads', {
      params: { per_page: 1, contact_status: tabFilterMap.archived },
    })
    counts.archived = data.meta.total
  } catch { /* */ }
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

async function fetchProducts() {
  try {
    const { data } = await api.get('/api/products')
    products.value = data.data
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
  if (activeTab.value !== 'prospecting') {
    fetchLeads()
  }
}

function handleTabChange(tab) {
  activeTab.value = tab
  page.value = 1
  fetchLeads()
}

onMounted(() => {
  fetchLeads()
  fetchTemplates()
  fetchTiers()
  fetchProducts()
  fetchCounts()
})
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Users class="w-6 h-6 text-vue-green shrink-0" />
        <h1 class="text-lg sm:text-xl font-bold text-on-surface font-display">Leads</h1>
      </div>
      <button
        @click="handleNewLead"
        class="flex items-center gap-1.5 sm:gap-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg text-xs sm:text-sm transition cursor-pointer whitespace-nowrap"
      >
        <Plus class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
        New Lead
      </button>
    </div>

    <div class="flex items-center gap-1 bg-surface-charcoal rounded-xl p-1 w-full overflow-x-auto border border-outline-variant/20">
      <button
        @click="handleTabChange('prospecting')"
        title="Prospección"
        class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition cursor-pointer whitespace-nowrap"
        :class="activeTab === 'prospecting'
          ? 'bg-vue-green text-forest-deep shadow-sm'
          : 'text-slate-text hover:text-on-surface'"
      >
        <Target class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
        <span class="hidden sm:inline">Prospección</span>
        <span
          class="text-xs px-1.5 py-0.5 rounded-full"
          :class="activeTab === 'prospecting' ? 'bg-forest-deep/20 text-forest-deep' : 'bg-surface-charcoal text-slate-text'"
        >
          {{ counts.prospecting }}
        </span>
      </button>
      <button
        @click="handleTabChange('clients')"
        title="Clientes"
        class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition cursor-pointer whitespace-nowrap"
        :class="activeTab === 'clients'
          ? 'bg-vue-green text-forest-deep shadow-sm'
          : 'text-slate-text hover:text-on-surface'"
      >
        <Star class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
        <span class="hidden sm:inline">Clientes</span>
        <span
          class="text-xs px-1.5 py-0.5 rounded-full"
          :class="activeTab === 'clients' ? 'bg-forest-deep/20 text-forest-deep' : 'bg-surface-charcoal text-slate-text'"
        >
          {{ counts.clients }}
        </span>
      </button>
      <button
        @click="handleTabChange('archived')"
        title="Archivados"
        class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition cursor-pointer whitespace-nowrap"
        :class="activeTab === 'archived'
          ? 'bg-vue-green text-forest-deep shadow-sm'
          : 'text-slate-text hover:text-on-surface'"
      >
        <Archive class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
        <span class="hidden sm:inline">Archivados</span>
        <span
          class="text-xs px-1.5 py-0.5 rounded-full"
          :class="activeTab === 'archived' ? 'bg-forest-deep/20 text-forest-deep' : 'bg-surface-charcoal text-slate-text'"
        >
          {{ counts.archived }}
        </span>
      </button>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1 w-full sm:max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-text" />
        <input
          v-model="search"
          @input="handleSearch"
          placeholder="Search leads..."
          class="w-full pl-10 pr-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none text-sm transition"
        />
      </div>
    </div>

    <LeadForm
      v-if="showForm"
      :tier-mapping="tierMapping"
      :products="products"
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

    <LeadKanban
      v-if="activeTab === 'prospecting'"
      :leads="leads"
      :templates="templates"
      :products="products"
      :search="search"
      @update-status="handleUpdateStatus"
      @edit="handleEdit"
      @delete="handleDelete"
      @contact="handleContact"
      @send-whats-app="handleSendWhatsApp"
    />

    <LeadTable
      v-if="activeTab !== 'prospecting'"
      :leads="leads"
      :loading="loading"
      :templates="templates"
      :products="products"
      :status-scope="statusScope"
      @contact="handleContact"
      @send-whats-app="handleSendWhatsApp"
      @update-status="handleUpdateStatus"
      @edit="handleEdit"
      @delete="handleDelete"
    />
  </div>
</template>
