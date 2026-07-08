<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Standorte</h1>
      <button class="btn-primary" @click="openCreate">+ Neuer Standort</button>
    </div>

    <div class="card">
      <div v-if="loading" class="py-12 text-center text-sm text-gray-400">Laden…</div>
      <template v-else>
        <LocationTreeNode
          v-for="loc in rootLocations"
          :key="loc.id"
          :location="loc"
          @edit="openEdit"
          @delete="deleteLocation"
        />
        <p v-if="rootLocations.length === 0" class="py-8 text-center text-sm text-gray-400">
          Keine Standorte vorhanden.
        </p>
      </template>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
          <h2 class="mb-4 text-lg font-semibold">{{ editing ? 'Standort bearbeiten' : 'Neuer Standort' }}</h2>
          <LocationForm
            :initial="editing"
            :locations="allLocations"
            :location-types="locationTypes"
            @saved="onSaved"
            @cancel="showModal = false"
          />
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLocations } from '@/composables/useApi'
import api from '@/composables/useApi'
import LocationTreeNode from './LocationTreeNode.vue'
import LocationForm from './LocationForm.vue'

const locationApi   = useLocations()
const allLocations  = ref([])
const locationTypes = ref([])
const loading       = ref(false)
const showModal     = ref(false)
const editing       = ref(null)

const rootLocations = computed(() =>
  allLocations.value.filter(l => !l.parent_location_id).map(l => withChildren(l))
)

function withChildren(loc) {
  return {
    ...loc,
    children: allLocations.value.filter(l => l.parent_location_id === loc.id).map(l => withChildren(l)),
  }
}

async function load() {
  loading.value = true
  const [locs, types] = await Promise.all([
    locationApi.list(),
    api.get('/location-type-definitions'),
  ])
  allLocations.value  = locs.data
  locationTypes.value = types.data
  loading.value = false
}

function openCreate() { editing.value = null; showModal.value = true }
function openEdit(loc) { editing.value = loc; showModal.value = true }

async function deleteLocation(loc) {
  if (!confirm(`Standort "${loc.name}" löschen?`)) return
  await locationApi.remove(loc.id)
  await load()
}

async function onSaved() {
  showModal.value = false
  await load()
}

onMounted(load)
</script>
