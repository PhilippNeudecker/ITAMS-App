<template>
  <div v-if="asset" class="space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-gray-400">{{ asset.category?.name }}</p>
        <h1 class="text-2xl font-semibold text-gray-900">{{ asset.name }}</h1>
        <span class="mt-1 font-mono text-sm text-indigo-500">{{ asset.asset_label }}</span>
      </div>
      <div class="flex gap-2">
        <router-link :to="`/assets/${asset.id}/edit`" class="btn-secondary">Bearbeiten</router-link>
        <button class="btn-danger" @click="confirmDelete">Löschen</button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Left: Main info -->
      <div class="space-y-6 lg:col-span-2">

        <div class="card">
          <h2 class="card-title">Stammdaten</h2>
          <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div><dt class="label">Status</dt><dd>{{ asset.status?.name ?? '—' }}</dd></div>
            <div><dt class="label">Hersteller</dt><dd>{{ asset.manufacturer?.name ?? '—' }}</dd></div>
            <div><dt class="label">Standort</dt><dd>{{ asset.location?.name ?? '—' }}</dd></div>
            <div><dt class="label">Kaufdatum</dt><dd>{{ formatDate(asset.purchase_date) }}</dd></div>
            <div><dt class="label">Kaufpreis</dt><dd>{{ formatCurrency(asset.purchase_value) }}</dd></div>
          </dl>
          <div class="mt-4 flex flex-wrap gap-1">
            <span
              v-for="tag in asset.tags"
              :key="tag.id"
              class="rounded-full px-2 py-0.5 text-xs font-medium"
              :style="tagStyle(tag.color)"
            >{{ tag.name }}</span>
          </div>
        </div>

        <div v-if="asset.property_values?.length" class="card">
          <h2 class="card-title">Eigenschaften</h2>
          <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div v-for="pv in asset.property_values" :key="pv.id">
              <dt class="label">
                {{ pv.name }}
                <span v-if="pv.unit" class="ml-1 text-gray-400">({{ pv.unit }})</span>
              </dt>
              <dd>{{ formatPropertyValue(pv) }}</dd>
            </div>
          </dl>
        </div>

        <div v-if="asset.supplier?.name" class="card">
          <h2 class="card-title">Lieferant</h2>
          <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div><dt class="label">Lieferantennr.</dt><dd>{{ asset.supplier.number ?? '—' }}</dd></div>
            <div><dt class="label">Name</dt><dd>{{ asset.supplier.name }}</dd></div>
            <div><dt class="label">Ort</dt><dd>{{ [asset.supplier.city, asset.supplier.country].filter(Boolean).join(', ') }}</dd></div>
          </dl>
        </div>
      </div>

      <!-- Right: Sidebar -->
      <div class="space-y-6">

        <div class="card">
          <h2 class="card-title">Garantie</h2>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="label">Von</span><span>{{ formatDate(asset.warranty_start_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="label">Bis</span><span>{{ formatDate(asset.warranty_end_date) }}</span>
            </div>
            <div class="mt-2">
              <span
                v-if="asset.warranty_end_date"
                class="inline-block rounded-full px-3 py-1 text-xs font-medium"
                :class="warrantyClass"
              >{{ warrantyLabel }}</span>
              <span v-else class="text-xs text-gray-400">Keine Garantie hinterlegt</span>
            </div>
          </div>
        </div>

        <div class="card">
          <h2 class="card-title">Zuweisung</h2>
          <div v-if="asset.active_assignment" class="space-y-1 text-sm">
            <p class="font-medium text-gray-900">
              {{ asset.active_assignment.employee_display_name ?? asset.active_assignment.cost_center_name }}
            </p>
            <p class="text-xs text-gray-400">seit {{ formatDate(asset.active_assignment.assigned_from) }}</p>
            <button class="btn-ghost mt-2 text-xs text-red-500" @click="unassign">Zuweisung beenden</button>
          </div>
          <div v-else class="text-sm text-gray-400">
            <p>Nicht zugewiesen</p>
            <button class="btn-ghost mt-2 text-xs text-indigo-600" @click="showAssignModal = true">Zuweisen</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Assign Modal -->
    <Teleport to="body">
      <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-semibold">Asset zuweisen</h3>
          <AssetAssignForm :asset-id="asset.id" @saved="onAssigned" @cancel="showAssignModal = false" />
        </div>
      </div>
    </Teleport>
  </div>

  <div v-else-if="loading" class="flex justify-center py-24 text-gray-400">Laden…</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAssets } from '@/composables/useApi'
import AssetAssignForm from './AssetAssignForm.vue'

const route  = useRoute()
const router = useRouter()
const api    = useAssets()

const asset           = ref(null)
const loading         = ref(false)
const showAssignModal = ref(false)

async function fetchAsset() {
  loading.value = true
  const { data } = await api.get(route.params.id)
  asset.value   = data
  loading.value = false
}

async function unassign() {
  if (!asset.value) return
  await api.unassign(asset.value.id)
  await fetchAsset()
}

async function confirmDelete() {
  if (!asset.value || !confirm(`Asset "${asset.value.name}" wirklich löschen?`)) return
  await api.remove(asset.value.id)
  router.push('/assets')
}

async function onAssigned() {
  showAssignModal.value = false
  await fetchAsset()
}

const warrantyClass = computed(() => {
  const days = asset.value?.warranty_expires_in_days
  if (days == null) return ''
  if (days < 0)  return 'bg-red-100 text-red-700'
  if (days < 30) return 'bg-orange-100 text-orange-700'
  return 'bg-green-100 text-green-700'
})

const warrantyLabel = computed(() => {
  const days = asset.value?.warranty_expires_in_days
  if (days == null) return ''
  if (days < 0)  return `Abgelaufen vor ${Math.abs(days)} Tagen`
  if (days === 0) return 'Läuft heute ab'
  return `Noch ${days} Tage`
})

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('de-DE')
}

function formatCurrency(v) {
  if (v == null) return '—'
  return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(v)
}

function formatPropertyValue(pv) {
  if (pv.typed_value == null) return '—'
  if (pv.data_type === 'Boolean') return pv.typed_value ? 'Ja' : 'Nein'
  if (pv.data_type === 'Date')    return formatDate(pv.typed_value)
  return String(pv.typed_value)
}

function tagStyle(color) {
  if (!color) return { backgroundColor: '#e5e7eb', color: '#374151' }
  return { backgroundColor: color + '25', color }
}

onMounted(fetchAsset)
</script>
