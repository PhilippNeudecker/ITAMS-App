<template>
  <form class="space-y-4" @submit.prevent="submit">
    <div class="grid grid-cols-2 gap-4">
      <div class="col-span-2">
        <label class="label">Name *</label>
        <input v-model="form.name" class="input w-full" required />
      </div>
      <div>
        <label class="label">Typ *</label>
        <select v-model="form.location_type_definition_id" class="input w-full" required>
          <option value="">— Auswählen —</option>
          <option v-for="t in locationTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>
      <div>
        <label class="label">Übergeordneter Standort</label>
        <select v-model="form.parent_location_id" class="input w-full">
          <option value="">— Kein (Root) —</option>
          <option v-for="l in availableParents" :key="l.id" :value="l.id">{{ l.name }}</option>
        </select>
      </div>
      <div><label class="label">Straße</label><input v-model="form.street" class="input w-full" /></div>
      <div><label class="label">Hausnummer</label><input v-model="form.house_number" class="input w-full" /></div>
      <div><label class="label">PLZ</label><input v-model="form.postal_code" class="input w-full" /></div>
      <div><label class="label">Stadt</label><input v-model="form.city" class="input w-full" /></div>
      <div class="col-span-2"><label class="label">Land</label><input v-model="form.country" class="input w-full" /></div>
      <div class="col-span-2"><label class="label">Zusatzinfo</label><textarea v-model="form.additional_info" class="input w-full" rows="2" /></div>
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
import { useLocations } from '@/composables/useApi'

const props = defineProps({
  initial:       { type: Object, default: null },
  locations:     { type: Array,  default: () => [] },
  locationTypes: { type: Array,  default: () => [] },
})
const emit = defineEmits(['saved', 'cancel'])

const api    = useLocations()
const saving = ref(false)
const error  = ref('')

const form = reactive({
  name:                        props.initial?.name ?? '',
  location_type_definition_id: props.initial?.location_type_definition_id ?? '',
  parent_location_id:          props.initial?.parent_location_id ?? '',
  street:                      props.initial?.street ?? '',
  house_number:                props.initial?.house_number ?? '',
  postal_code:                 props.initial?.postal_code ?? '',
  city:                        props.initial?.city ?? '',
  country:                     props.initial?.country ?? '',
  additional_info:             props.initial?.additional_info ?? '',
})

const availableParents = computed(() => props.locations.filter(l => l.id !== props.initial?.id))

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
