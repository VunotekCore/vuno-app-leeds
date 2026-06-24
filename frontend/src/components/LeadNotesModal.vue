<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { X, MessageSquareText, Trash2, Loader, Send, AlertCircle } from '@lucide/vue'

const props = defineProps({
  lead: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const notes = ref([])
const loading = ref(true)
const newNote = ref('')
const submitting = ref(false)
const error = ref('')

onMounted(fetchNotes)

async function fetchNotes() {
  loading.value = true
  try {
    const { data } = await api.get(`/api/leads/${props.lead.id}/notes`)
    notes.value = data.data.notes
  } catch {
    error.value = 'Failed to load notes'
  } finally {
    loading.value = false
  }
}

async function addNote() {
  if (!newNote.value.trim()) return
  submitting.value = true
  error.value = ''
  try {
    const { data } = await api.post(`/api/leads/${props.lead.id}/notes`, {
      note: newNote.value.trim(),
    })
    notes.value = data.data.notes
    newNote.value = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to add note'
  } finally {
    submitting.value = false
  }
}

async function deleteNote(noteId) {
  try {
    await api.delete(`/api/leads/${props.lead.id}/notes/${noteId}`)
    notes.value = notes.value.filter(n => n.id !== noteId)
  } catch {
    error.value = 'Failed to delete note'
  }
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-start justify-center pt-12 pb-12 bg-black/60 overflow-y-auto backdrop-blur-sm">
    <div class="glass-panel rounded-xl shadow-xl w-full max-w-lg mx-4">
      <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/20">
        <div class="flex items-center gap-2">
          <MessageSquareText class="w-5 h-5 text-vue-green" />
          <h2 class="text-lg font-bold text-on-surface font-display">Notes</h2>
        </div>
        <button @click="emit('close')" class="text-slate-text hover:text-on-surface transition cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="px-6 py-3 border-b border-outline-variant/10 bg-surface-container/30">
        <p class="text-sm font-medium text-on-surface">{{ lead.store_name }}</p>
        <p v-if="lead.phone" class="text-xs text-slate-text">+{{ lead.phone }}</p>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <Loader class="w-6 h-6 text-vue-green animate-spin" />
      </div>

      <div v-else class="max-h-[50vh] overflow-y-auto p-6 space-y-3">
        <div v-if="notes.length === 0" class="text-center py-8 text-slate-text text-sm">
          <p>No notes yet</p>
          <p class="text-xs mt-1">Add the first note below</p>
        </div>

        <div
          v-for="note in notes"
          :key="note.id"
          class="bg-surface-charcoal/50 rounded-lg p-3 border border-outline-variant/10"
        >
          <p class="text-sm text-on-surface whitespace-pre-wrap">{{ note.note }}</p>
          <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-slate-text">{{ formatDate(note.created_at) }}</span>
            <div class="flex items-center gap-2">
              <span v-if="note.created_by_email" class="text-xs text-slate-text/60">{{ note.created_by_email }}</span>
              <button
                @click="deleteNote(note.id)"
                class="p-1 text-slate-text hover:text-error rounded hover:bg-error/10 transition cursor-pointer"
                title="Delete note"
              >
                <Trash2 class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="error" class="px-6 pb-2">
        <div class="flex items-center gap-2 bg-error-container/20 border border-error/30 rounded-lg px-3 py-2">
          <AlertCircle class="w-4 h-4 text-error shrink-0" />
          <span class="text-xs text-error">{{ error }}</span>
        </div>
      </div>

      <form @submit.prevent="addNote" class="border-t border-outline-variant/20 p-4 flex gap-2">
        <textarea
          v-model="newNote"
          placeholder="Write a note..."
          rows="2"
          class="flex-1 px-3 py-2 bg-surface-charcoal border border-outline-variant/50 rounded-lg text-on-surface placeholder-slate-text/50 text-sm focus:ring-2 focus:ring-vue-green/40 focus:border-vue-green outline-none transition resize-none"
        />
        <button
          type="submit"
          :disabled="submitting || !newNote.trim()"
          class="self-end px-4 py-2 bg-vue-green hover:bg-node-green disabled:opacity-40 disabled:cursor-not-allowed text-forest-deep font-semibold text-sm rounded-lg transition cursor-pointer flex items-center gap-1.5"
        >
          <Send v-if="!submitting" class="w-3.5 h-3.5" />
          <Loader v-else class="w-3.5 h-3.5 animate-spin" />
          Send
        </button>
      </form>
    </div>
  </div>
</template>
