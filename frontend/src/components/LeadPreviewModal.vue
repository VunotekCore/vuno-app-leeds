<script setup>
import { X, ExternalLink, Pencil, Globe, Calendar, Smartphone, Mail, Users, Target, Hash, Clock } from '@lucide/vue'

const props = defineProps({
  lead: { type: Object, required: true },
})

const emit = defineEmits(['close', 'edit'])

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatFollowers(count) {
  if (!count && count !== 0) return '-'
  return Number(count).toLocaleString()
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-start justify-center pt-8 pb-8 bg-black/60 overflow-y-auto backdrop-blur-sm">
    <div class="glass-panel rounded-xl shadow-xl w-full max-w-lg mx-4 my-auto">
      <!-- Header -->
      <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-outline-variant/20">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-full bg-vue-green/15 flex items-center justify-center shrink-0">
            <Users class="w-5 h-5 text-vue-green" />
          </div>
          <div class="min-w-0">
            <h2 class="text-lg font-bold text-on-surface font-display truncate">{{ lead.store_name }}</h2>
            <p class="text-xs text-slate-text truncate">Lead details</p>
          </div>
        </div>
        <button
          @click="emit('close')"
          class="p-1.5 text-slate-text hover:text-on-surface rounded-lg hover:bg-surface-charcoal transition cursor-pointer shrink-0"
          title="Close"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Body -->
      <div class="p-4 sm:p-6 space-y-5 max-h-[70vh] overflow-y-auto">
        <!-- Contact Info -->
        <div>
          <h3 class="text-xs font-semibold text-slate-text uppercase tracking-wider mb-3">Contact</h3>
          <div class="bg-surface-charcoal/50 rounded-xl divide-y divide-outline-variant/10">
            <div class="flex items-center gap-3 px-4 py-3">
              <Smartphone class="w-4 h-4 text-vue-green shrink-0" />
              <span class="text-xs text-slate-text w-16 shrink-0">Phone</span>
              <span class="text-sm text-on-surface font-medium truncate">{{ lead.phone ? `+${lead.phone}` : '-' }}</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <Mail class="w-4 h-4 text-blue-400 shrink-0" />
              <span class="text-xs text-slate-text w-16 shrink-0">Email</span>
              <span class="text-sm text-on-surface font-medium truncate">{{ lead.email || '-' }}</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <Globe class="w-4 h-4 text-purple-400 shrink-0" />
              <span class="text-xs text-slate-text w-16 shrink-0">Profile</span>
              <a
                v-if="lead.profile_url"
                :href="lead.profile_url"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm text-vue-green hover:text-node-green font-medium truncate hover:underline inline-flex items-center gap-1"
              >
                {{ lead.profile_url }}
                <ExternalLink class="w-3 h-3 shrink-0" />
              </a>
              <span v-else class="text-sm text-slate-text">-</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <Users class="w-4 h-4 text-yellow-400 shrink-0" />
              <span class="text-xs text-slate-text w-16 shrink-0">Followers</span>
              <span class="text-sm text-on-surface font-medium">{{ formatFollowers(lead.followers_count) }}</span>
            </div>
          </div>
        </div>

        <!-- Classification -->
        <div>
          <h3 class="text-xs font-semibold text-slate-text uppercase tracking-wider mb-3">Classification</h3>
          <div class="bg-surface-charcoal/50 rounded-xl divide-y divide-outline-variant/10">
            <div class="flex items-center gap-3 px-4 py-3">
              <Target class="w-4 h-4 text-emerald-400 shrink-0" />
              <span class="text-xs text-slate-text w-20 shrink-0">Status</span>
              <span class="text-sm text-on-surface font-medium">{{ lead.contact_status }}</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <Hash class="w-4 h-4 text-indigo-400 shrink-0" />
              <span class="text-xs text-slate-text w-20 shrink-0">Tier</span>
              <span class="text-sm text-on-surface font-medium">{{ lead.tier_classification || '-' }}</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <span class="w-4 h-4 shrink-0 flex items-center justify-center text-xs font-bold text-vue-green">P</span>
              <span class="text-xs text-slate-text w-20 shrink-0">Product</span>
              <span class="text-sm text-on-surface font-medium truncate">{{ lead.product_name || '-' }}</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <span class="w-4 h-4 shrink-0 flex items-center justify-center text-xs font-bold text-indigo-400">C</span>
              <span class="text-xs text-slate-text w-20 shrink-0">Category</span>
              <span class="text-sm text-on-surface font-medium">{{ lead.category_name || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Activity -->
        <div>
          <h3 class="text-xs font-semibold text-slate-text uppercase tracking-wider mb-3">Activity</h3>
          <div class="bg-surface-charcoal/50 rounded-xl divide-y divide-outline-variant/10">
            <div class="flex items-center gap-3 px-4 py-3">
              <Clock class="w-4 h-4 text-slate-text shrink-0" />
              <span class="text-xs text-slate-text w-24 shrink-0">Registered</span>
              <span class="text-sm text-on-surface font-medium">{{ formatDate(lead.registration_date) }}</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <Calendar class="w-4 h-4 text-slate-text shrink-0" />
              <span class="text-xs text-slate-text w-24 shrink-0">Last Contact</span>
              <span class="text-sm text-on-surface font-medium">{{ formatDate(lead.last_contact_date) }}</span>
            </div>
            <div class="flex items-center gap-3 px-4 py-3">
              <Smartphone class="w-4 h-4 text-slate-text shrink-0" />
              <span class="text-xs text-slate-text w-24 shrink-0">Attempts</span>
              <span class="text-sm text-on-surface font-medium">{{ lead.contact_attempts ?? 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-outline-variant/20">
        <button
          @click="emit('close')"
          class="px-4 py-2 text-sm text-slate-text hover:text-on-surface rounded-lg hover:bg-surface-charcoal transition cursor-pointer"
        >
          Close
        </button>
        <button
          @click="emit('edit', lead)"
          class="flex items-center gap-2 px-4 py-2 bg-vue-green hover:bg-node-green text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer"
        >
          <Pencil class="w-4 h-4" />
          Edit Lead
        </button>
      </div>
    </div>
  </div>
</template>
