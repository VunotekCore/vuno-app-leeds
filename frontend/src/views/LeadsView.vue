<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../services/api'
import LeadForm from '../components/LeadForm.vue'
import LeadKanban from '../components/LeadKanban.vue'
import LeadTable from '../components/LeadTable.vue'
import LeadContactModal from '../components/LeadContactModal.vue'
import { Plus, Search, Users, Target, Star, Archive } from '@lucide/vue'

const allLeads = ref({ prospecting: [], clients: [], archived: [] })
const templates = ref([])
const tiers = ref([])
const products = ref([])
const categories = ref([])
const counts = reactive({ prospecting: 0, clients: 0, archived: 0 })
const loading = ref(true)
const search = ref('')
const activeTab = ref('prospecting')
const showForm = ref(false)
const editingLead = ref(null)
const contactingLead = ref(null)

const leads = computed(() => {
  const group = allLeads.value[activeTab.value]
  if (!group) return []
  if (!search.value) return group
  const q = search.value.toLowerCase()
  return group.filter(l =>
    l.store_name?.toLowerCase().includes(q) ||
    l.phone?.includes(q) ||
    l.email?.toLowerCase().includes(q) ||
    l.product_name?.toLowerCase().includes(q) ||
    l.category_name?.toLowerCase().includes(q)
  )
})

const tierMapping = computed(() => {
  const map = {}
  for (const t of tiers.value) {
    map[t.name] = `$${parseFloat(t.price).toFixed(2)} USD`
  }
  return map
})

async function fetchPageData() {
  loading.value = true
  try {
    const { data } = await api.get('/api/leads/page-data')
    allLeads.value = data.data.leads
    counts.prospecting = data.data.counts.prospecting
    counts.clients = data.data.counts.clients
    counts.archived = data.data.counts.archived
    templates.value = data.data.templates
    tiers.value = data.data.tiers
    products.value = data.data.products
    categories.value = data.data.categories
  } catch {
    //
  } finally {
    loading.value = false
  }
}

async function handleDelete(lead) {
  if (!confirm(`Delete lead "${lead.store_name}"?`)) return
  try {
    await api.delete(`/api/leads/${lead.id}`)
    await fetchPageData()
  } catch {
    //
  }
}

async function handleUpdateStatus(lead) {
  try {
    await api.put(`/api/leads/${lead.id}`, {
      contact_status: lead.contact_status,
    })
    await fetchPageData()
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
    await fetchPageData()
  } catch {
    //
  }
}

function handleLeadCreated() {
  showForm.value = false
  editingLead.value = null
  fetchPageData()
}

function handleLeadUpdated() {
  showForm.value = false
  editingLead.value = null
  fetchPageData()
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
  fetchPageData()
}

function handleTabChange(tab) {
  activeTab.value = tab
}

onMounted(fetchPageData)
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
          placeholder="Search leads..."
          class="w-full pl-10 pr-4 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none text-sm transition"
        />
      </div>
    </div>

    <LeadForm
      v-if="showForm"
      :tier-mapping="tierMapping"
      :products="products"
      :categories="categories"
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
      :categories="categories"
      :status-scope="activeTab"
      @contact="handleContact"
      @send-whats-app="handleSendWhatsApp"
      @update-status="handleUpdateStatus"
      @edit="handleEdit"
      @delete="handleDelete"
    />
  </div>
</template>
