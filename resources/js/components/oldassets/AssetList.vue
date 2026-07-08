<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Assets</h1>
      <router-link to="/assets/create" class="btn-primary">+ Neues Asset</router-link>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-wrap gap-3 rounded-lg border border-gray-200 bg-white p-4">
      <input
        v-model="filters.search"
        type="text"
        placeholder="Suche nach Name, Label…"
        class="input w-64"
        @input="debouncedLoad"
      />
      <select v-model="filters.category_id" class="input w-48" @change="load">
        <option value="">Alle Kategorien</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
      <select v-model="filters.status_id" class="input w-48" @change="load">
        <option value="">Alle Status</option>
        <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
      <button class="btn-ghost text-sm" @click="resetFilters">Filter zurücksetzen</button>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
      <div v-if="loading" class="flex justify-center py-16 text-gray-400">
        <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="border-b border-gray-100 bg-gray-50 text-xs font-medium uppercase text-gray-500">
          <tr>
            <th class="px-4 py-3 text-left">Label</th>
            <th class="px-4 py-3 text-left">Name</th>
            <th class="px-4 py-3 text-left">Kategorie</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Standort</th>
            <th class="px-4 py-3 text-left">Zugewiesen an</th>
            <th class="px-4 py-3 text-left">Tags</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="asset in assets"
            :key="asset.id"
            class="cursor-pointer hover:bg-gray-50"
            @click="$router.push(`/assets/${asset.id}`)"
          >
            <td class="px-4 py-3 font-mono text-xs font-medium text-indigo-600">{{ asset.asset_label }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ asset.name }}</td>
            <td class="px-4 py-3">
              <span
                v-if="asset.category"
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                :style="categoryStyle(asset.category.color)"
              >{{ asset.category.name }}</span>
            </td>
            <td class="px-4 py-3 text-gray-700">{{ asset.status?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-500">{{ asset.location?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-500">
              {{ asset.active_assignment?.employee_display_name
                ?? asset.active_assignment?.cost_center_name
                ?? '—' }}
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="tag in asset.tags?.slice(0, 3)"
                  :key="tag.id"
                  class="rounded-full px-1.5 py-0.5 text-xs font-medium"
                  :style="tagStyle(tag.color)"
                >{{ tag.name }}</span>
                <span v-if="(asset.tags?.length ?? 0) > 3" class="text-xs text-gray-400">
                  +{{ (asset.tags?.length ?? 0) - 3 }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3 text-right" @click.stop>
              <router-link :to="`/assets/${asset.id}/edit`" class="text-xs text-indigo-600 hover:underline">
                Bearbeiten
              </router-link>
            </td>
          </tr>
          <tr v-if="assets.length === 0">
            <td colspan="8" class="px-4 py-12 text-center text-gray-400">Keine Assets gefunden.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="pagination && pagination.last_page > 1"
        class="flex items-center justify-between border-t border-gray-100 px-4 py-3 text-sm text-gray-500"
      >
        <span>{{ pagination.total }} Einträge insgesamt</span>
        <div class="flex gap-2">
          <button :disabled="pagination.current_page === 1" class="btn-ghost disabled:opacity-40" @click="changePage(pagination.current_page - 1)">
            ← Zurück
          </button>
          <span class="px-2 py-1 text-gray-700">Seite {{ pagination.current_page }} / {{ pagination.last_page }}</span>
          <button :disabled="pagination.current_page === pagination.last_page" class="btn-ghost disabled:opacity-40" @click="changePage(pagination.current_page + 1)">
            Weiter →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAssets, useCategories } from '@/composables/useApi'
import api from '@/composables/useApi'

const assetApi    = useAssets()
const categoryApi = useCategories()

const assets     = ref([])
const categories = ref([])
const statuses   = ref([])
const loading    = ref(false)
const pagination = ref(null)

const filters = reactive({
  search:      '',
  category_id: '',
  status_id:   '',
  page:        1,
  per_page:    25,
})

let debounceTimer
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { filters.page = 1; load() }, 350)
}

async function load() {
  loading.value = true
  try {
    const { data } = await assetApi.list(filters)
    assets.value     = data.data
    pagination.value = data.meta
  } finally {
    loading.value = false
  }
}

function changePage(page) {
  filters.page = page
  load()
}

function resetFilters() {
  filters.search = ''
  filters.category_id = ''
  filters.status_id = ''
  filters.page = 1
  load()
}

function categoryStyle(color) {
  if (!color) return {}
  return { backgroundColor: color + '20', color }
}

function tagStyle(color) {
  if (!color) return { backgroundColor: '#e5e7eb', color: '#374151' }
  return { backgroundColor: color + '25', color }
}

onMounted(async () => {
  const [cats, stats] = await Promise.all([
    categoryApi.list(),
    api.get('/status-definitions', { params: { module: 'Asset' } }),
    load(),
  ])
  categories.value = cats.data
  statuses.value   = stats.data
})
</script>
