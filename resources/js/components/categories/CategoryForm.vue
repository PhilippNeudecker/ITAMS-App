<template>
  <form class="space-y-4" @submit.prevent="submit">
    <div class="grid grid-cols-2 gap-4">
      <div class="col-span-2">
        <label class="label">Name *</label>
        <input v-model="form.name" class="input w-full" required />
      </div>
      <div class="col-span-2">
        <label class="label">Beschreibung</label>
        <textarea v-model="form.description" class="input w-full" rows="2" />
      </div>
      <div>
        <label class="label">Übergeordnete Kategorie</label>
        <select v-model="form.parent_category_id" class="input w-full">
          <option value="">— Keine (Root) —</option>
          <option v-for="c in availableParents" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div>
        <label class="label">Farbe</label>
        <div class="flex items-center gap-2">
          <input v-model="form.color" type="color" class="h-9 w-12 rounded border border-gray-200 p-0.5" />
          <input v-model="form.color" class="input flex-1 font-mono text-xs" placeholder="#3B82F6" />
        </div>
      </div>
      <div>
        <label class="label">Präfix *</label>
        <input v-model="form.asset_prefix" class="input w-full font-mono uppercase" maxlength="9" required placeholder="LAP" />
      </div>
      <div>
        <label class="label">Separator</label>
        <input v-model="form.asset_separator" class="input w-full font-mono" maxlength="5" placeholder="-" />
      </div>
      <div>
        <label class="label">Nummernlänge *</label>
        <input v-model.number="form.asset_number_length" type="number" min="1" max="10" class="input w-full" required />
      </div>
      <div>
        <label class="label">Vorschau Label</label>
        <span class="input flex items-center bg-gray-50 font-mono text-sm text-gray-600">{{ labelPreview }}</span>
      </div>
      <div>
        <label class="label">Garantie Standard (Tage)</label>
        <input v-model.number="form.default_warranty_days" type="number" min="0" class="input w-full" />
      </div>
      <div>
        <label class="label">Garantie-Erinnerung (Tage vorher)</label>
        <input v-model.number="form.default_warranty_notify_days_before" type="number" min="0" class="input w-full" />
      </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
      <button type="button" class="btn-secondary" @click="$emit('cancel')">Abbrechen</button>
      <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Speichern…' : 'Speichern' }}</button>
    </div>
    <p v-if="error" class="text-right text-sm text-red-500">{{ error }}</p>
  </form>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useCategories } from '@/composables/useApi'

const props = defineProps({
  initial:    { type: Object, default: null },
  categories: { type: Array,  default: () => [] },
})
const emit = defineEmits(['saved', 'cancel'])

const api    = useCategories()
const saving = ref(false)
const error  = ref('')

const form = reactive({
  name:                                props.initial?.name ?? '',
  description:                         props.initial?.description ?? '',
  color:                               props.initial?.color ?? '#6366F1',
  parent_category_id:                  props.initial?.parent_category_id ?? '',
  asset_prefix:                        props.initial?.asset_prefix ?? '',
  asset_separator:                     props.initial?.asset_separator ?? '-',
  asset_number_length:                 props.initial?.asset_number_length ?? 6,
  default_warranty_days:               props.initial?.default_warranty_days ?? null,
  default_warranty_notify_days_before: props.initial?.default_warranty_notify_days_before ?? null,
})

const availableParents = computed(() => props.categories.filter(c => c.id !== props.initial?.id))

const labelPreview = computed(() => {
  const prefix = form.asset_prefix || 'XXX'
  const sep    = form.asset_separator || '-'
  const len    = form.asset_number_length || 6
  return `${prefix}${sep}${'0'.repeat(len - 1)}1`
})

async function submit() {
  saving.value = true
  error.value  = ''
  try {
    if (props.initial) {
      await api.update(props.initial.id, form)
    } else {
      await api.create(form)
    }
    emit('saved')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Fehler beim Speichern.'
  } finally {
    saving.value = false
  }
}
</script>
