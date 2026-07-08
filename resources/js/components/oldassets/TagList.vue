<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Tags</h1>
      <button class="btn-primary" @click="openCreate">+ Neuer Tag</button>
    </div>

    <div class="card">
      <div class="flex flex-wrap gap-2 py-2">
        <div
          v-for="tag in tags"
          :key="tag.id"
          class="group flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm transition hover:shadow-sm"
          :style="{ borderColor: tag.color ?? '#e5e7eb', backgroundColor: (tag.color ?? '#e5e7eb') + '18' }"
        >
          <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: tag.color ?? '#9ca3af' }" />
          <span class="font-medium" :style="{ color: tag.color ?? '#374151' }">{{ tag.name }}</span>
          <span v-if="!tag.is_active" class="text-xs text-gray-400">(inaktiv)</span>
          <div class="ml-1 hidden gap-1 group-hover:flex">
            <button class="text-xs text-gray-500 hover:text-indigo-600" @click="openEdit(tag)">✎</button>
            <button class="text-xs text-gray-500 hover:text-red-500" @click="deleteTag(tag)">×</button>
          </div>
        </div>
        <p v-if="tags.length === 0" class="py-4 text-sm text-gray-400">Keine Tags vorhanden.</p>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
          <h2 class="mb-4 text-lg font-semibold">{{ editing ? 'Tag bearbeiten' : 'Neuer Tag' }}</h2>
          <form class="space-y-4" @submit.prevent="save">
            <div><label class="label">Name *</label><input v-model="form.name" class="input w-full" required /></div>
            <div><label class="label">Beschreibung</label><input v-model="form.description" class="input w-full" /></div>
            <div>
              <label class="label">Farbe</label>
              <div class="flex items-center gap-2">
                <input v-model="form.color" type="color" class="h-9 w-12 rounded border p-0.5" />
                <input v-model="form.color" class="input flex-1 font-mono text-xs" />
              </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.is_active" type="checkbox" class="rounded" /> Aktiv
            </label>
            <div class="flex justify-end gap-2">
              <button type="button" class="btn-secondary" @click="showModal = false">Abbrechen</button>
              <button type="submit" class="btn-primary">Speichern</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useTags } from '@/composables/useApi'

const api       = useTags()
const tags      = ref([])
const showModal = ref(false)
const editing   = ref(null)

const form = reactive({ name: '', description: '', color: '#6366F1', is_active: true })

function openCreate() {
  editing.value = null
  Object.assign(form, { name: '', description: '', color: '#6366F1', is_active: true })
  showModal.value = true
}

function openEdit(tag) {
  editing.value = tag
  Object.assign(form, { name: tag.name, description: tag.description ?? '', color: tag.color ?? '#6366F1', is_active: tag.is_active })
  showModal.value = true
}

async function save() {
  if (editing.value) {
    await api.update(editing.value.id, form)
  } else {
    await api.create(form)
  }
  showModal.value = false
  await load()
}

async function deleteTag(tag) {
  if (!confirm(`Tag "${tag.name}" wirklich löschen?`)) return
  await api.remove(tag.id)
  await load()
}

async function load() {
  const { data } = await api.list()
  tags.value = data
}

onMounted(load)
</script>
