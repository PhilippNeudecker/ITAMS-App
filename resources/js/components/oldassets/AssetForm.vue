<template>
  <form class="space-y-8" @submit.prevent="submit">

    <!-- Stammdaten -->
    <div class="card">
      <h2 class="card-title">Stammdaten</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="label">Name *</label>
          <input v-model="form.name" type="text" class="input w-full" required />
        </div>
        <div class="sm:col-span-2">
          <label class="label">Beschreibung</label>
          <textarea v-model="form.description" class="input w-full" rows="2" />
        </div>
        <div>
          <label class="label">Kategorie *</label>
          <select v-model="form.category_id" class="input w-full" required @change="onCategoryChange">
            <option value="">— Auswählen —</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="label">Status *</label>
          <select v-model="form.status_definition_id" class="input w-full" required>
            <option value="">— Auswählen —</option>
            <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="label">Hersteller</label>
          <select v-model="form.manufacturer_id" class="input w-full">
            <option value="">— Kein —</option>
            <option v-for="m in manufacturers" :key="m.id" :value="m.id">{{ m.name }}</option>
          </select>
        </div>
        <div>
          <label class="label">Standort</label>
          <select v-model="form.current_location_id" class="input w-full">
            <option value="">— Kein —</option>
            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
      </div>

      <!-- Tags -->
      <div class="mt-4">
        <label class="label">Tags</label>
        <div class="flex flex-wrap gap-2">
          <label
            v-for="tag in allTags"
            :key="tag.id"
            class="flex cursor-pointer items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs transition"
            :class="form.tag_ids.includes(tag.id)
              ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
              : 'border-gray-200 text-gray-600 hover:border-gray-400'"
          >
            <input type="checkbox" class="hidden" :value="tag.id" v-model="form.tag_ids" />
            <span v-if="tag.color" class="h-2 w-2 rounded-full" :style="{ backgroundColor: tag.color }" />
            {{ tag.name }}
          </label>
        </div>
      </div>
    </div>

    <!-- Kategorie-Eigenschaften -->
    <div v-if="categoryProperties.length" class="card">
      <h2 class="card-title">Kategorie-Eigenschaften</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div v-for="prop in categoryProperties" :key="prop.id">
          <label class="label">
            {{ prop.name }}
            <span v-if="prop.pivot?.is_required" class="text-red-500"> *</span>
            <span v-if="prop.unit" class="ml-1 text-gray-400">({{ prop.unit }})</span>
          </label>
          <input v-if="prop.data_type === 'Text'"
            v-model="propertyValues[prop.id].value_text"
            type="text" class="input w-full" :required="prop.pivot?.is_required" />
          <input v-else-if="prop.data_type === 'Number'"
            v-model.number="propertyValues[prop.id].value_number"
            type="number" step="any" class="input w-full" :required="prop.pivot?.is_required" />
          <input v-else-if="prop.data_type === 'Date'"
            v-model="propertyValues[prop.id].value_date"
            type="date" class="input w-full" :required="prop.pivot?.is_required" />
          <div v-else-if="prop.data_type === 'Boolean'" class="mt-1 flex items-center gap-2">
            <input v-model="propertyValues[prop.id].value_bool" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
          </div>
          <select v-else-if="prop.data_type === 'Option'"
            v-model="propertyValues[prop.id].property_option_id"
            class="input w-full" :required="prop.pivot?.is_required">
            <option value="">— Auswählen —</option>
            <option v-for="opt in prop.options" :key="opt.id" :value="opt.id">{{ opt.option_value }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Einkauf & Garantie -->
    <div class="card">
      <h2 class="card-title">Einkauf & Garantie</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div><label class="label">Kaufpreis (€)</label><input v-model.number="form.purchase_value" type="number" step="0.01" min="0" class="input w-full" /></div>
        <div><label class="label">Kaufdatum</label><input v-model="form.purchase_date" type="date" class="input w-full" /></div>
        <div><label class="label">Garantie von</label><input v-model="form.warranty_start_date" type="date" class="input w-full" /></div>
        <div><label class="label">Garantie bis</label><input v-model="form.warranty_end_date" type="date" class="input w-full" /></div>
        <div><label class="label">Garantie-Erinnerung (Tage vorher)</label><input v-model.number="form.warranty_notify_days_before" type="number" min="0" class="input w-full" /></div>
      </div>
    </div>

    <!-- Lieferant -->
    <div class="card">
      <h2 class="card-title">Lieferant</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div><label class="label">Lieferantennr.</label><input v-model="form.supplier_number" class="input w-full" /></div>
        <div><label class="label">Name</label><input v-model="form.supplier_name" class="input w-full" /></div>
        <div class="sm:col-span-2"><label class="label">Adresse</label><input v-model="form.supplier_address" class="input w-full" /></div>
        <div><label class="label">PLZ</label><input v-model="form.supplier_post_code" class="input w-full" /></div>
        <div><label class="label">Stadt</label><input v-model="form.supplier_city" class="input w-full" /></div>
        <div><label class="label">Land</label><input v-model="form.supplier_country" class="input w-full" /></div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3">
      <router-link :to="cancelRoute" class="btn-secondary">Abbrechen</router-link>
      <button type="submit" class="btn-primary" :disabled="saving">
        {{ saving ? 'Speichern…' : (isEdit ? 'Änderungen speichern' : 'Asset erstellen') }}
      </button>
    </div>
    <p v-if="error" class="text-right text-sm text-red-500">{{ error }}</p>
  </form>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAssets, useCategories, useTags, useLocations, useManufacturers } from '@/composables/useApi'
import api from '@/composables/useApi'

const props = defineProps({ asset: { type: Object, default: null } })
const emit  = defineEmits(['saved'])

const router      = useRouter()
const assetApi    = useAssets()
const categoryApi = useCategories()

const isEdit      = computed(() => !!props.asset)
const cancelRoute = computed(() => props.asset ? `/assets/${props.asset.id}` : '/assets')
const saving      = ref(false)
const error       = ref('')

const categories    = ref([])
const allTags       = ref([])
const locations     = ref([])
const manufacturers = ref([])
const statuses      = ref([])

const categoryProperties = ref([])
const propertyValues     = reactive({})

const form = reactive({
  name:                        props.asset?.name ?? '',
  description:                 props.asset?.description ?? '',
  category_id:                 props.asset?.category?.id ?? '',
  status_definition_id:        props.asset?.status?.id ?? '',
  manufacturer_id:             props.asset?.manufacturer?.id ?? '',
  current_location_id:         props.asset?.location?.id ?? '',
  tag_ids:                     props.asset?.tags?.map(t => t.id) ?? [],
  purchase_value:              props.asset?.purchase_value ?? null,
  purchase_date:               props.asset?.purchase_date ?? '',
  warranty_start_date:         props.asset?.warranty_start_date ?? '',
  warranty_end_date:           props.asset?.warranty_end_date ?? '',
  warranty_notify_days_before: props.asset?.warranty_notify_days_before ?? null,
  supplier_number:             props.asset?.supplier?.number ?? '',
  supplier_name:               props.asset?.supplier?.name ?? '',
  supplier_address:            props.asset?.supplier?.address ?? '',
  supplier_post_code:          '',
  supplier_city:               props.asset?.supplier?.city ?? '',
  supplier_country:            props.asset?.supplier?.country ?? '',
})

async function onCategoryChange() {
  if (!form.category_id) { categoryProperties.value = []; return }
  const { data } = await categoryApi.getProperties(form.category_id)
  categoryProperties.value = data
  for (const prop of data) {
    const existing = props.asset?.property_values?.find(pv => pv.property_definition_id === prop.id)
    propertyValues[prop.id] = {
      property_definition_id: prop.id,
      value_text:          existing ? String(existing.typed_value ?? '') : (prop.pivot?.default_value_text ?? ''),
      value_number:        existing ? Number(existing.typed_value)        : (prop.pivot?.default_value_number ?? null),
      value_date:          existing ? String(existing.typed_value ?? '') : (prop.pivot?.default_value_date ?? ''),
      value_bool:          existing ? Boolean(existing.typed_value)       : (prop.pivot?.default_value_bool ?? false),
      property_option_id:  prop.pivot?.default_property_option_id ?? '',
    }
  }
}

async function submit() {
  saving.value = true
  error.value  = ''
  try {
    const properties = Object.values(propertyValues)
    const payload    = { ...form, properties }
    let saved
    if (isEdit.value) {
      const { data } = await assetApi.update(props.asset.id, payload)
      saved = data
    } else {
      const { data } = await assetApi.create(payload)
      saved = data
    }
    emit('saved', saved)
    router.push(`/assets/${saved.id}`)
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Fehler beim Speichern.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const [cats, tags, locs, mans, stats] = await Promise.all([
    categoryApi.list(),
    useTags().list({ active_only: true }),
    useLocations().list(),
    useManufacturers().list(),
    api.get('/status-definitions', { params: { module: 'Asset' } }),
  ])
  categories.value    = cats.data
  allTags.value       = tags.data
  locations.value     = locs.data
  manufacturers.value = mans.data
  statuses.value      = stats.data

  if (form.category_id) await onCategoryChange()
})
</script>
