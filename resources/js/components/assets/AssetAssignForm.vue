<template>
  <form class="space-y-4" @submit.prevent="submit">
    <div>
      <label class="label">Zuweisungstyp *</label>
      <div class="flex gap-4">
        <label class="flex cursor-pointer items-center gap-1.5 text-sm">
          <input v-model="form.assignment_type" type="radio" value="User" class="text-indigo-600" /> Mitarbeiter
        </label>
        <label class="flex cursor-pointer items-center gap-1.5 text-sm">
          <input v-model="form.assignment_type" type="radio" value="CostCenter" class="text-indigo-600" /> Kostenstelle
        </label>
      </div>
    </div>

    <!-- Mitarbeiter-Suche -->
    <div v-if="form.assignment_type === 'User'">
      <label class="label">Mitarbeiter *</label>
      <input
        v-model="employeeSearch"
        type="text"
        class="input w-full"
        placeholder="Name oder Personalnummer suchen…"
        @input="searchEmployees"
      />
      <ul v-if="employeeResults.length" class="mt-1 divide-y rounded-lg border border-gray-200 bg-white shadow-sm">
        <li
          v-for="emp in employeeResults"
          :key="emp.id"
          class="cursor-pointer px-3 py-2 text-sm hover:bg-indigo-50"
          @click="selectEmployee(emp)"
        >
          <span class="font-medium">{{ emp.display_name }}</span>
          <span class="ml-2 text-xs text-gray-400">{{ emp.employee_id }} · {{ emp.mail }}</span>
        </li>
      </ul>
      <p v-if="selectedEmployee" class="mt-2 text-sm font-medium text-indigo-700">
        ✓ {{ selectedEmployee.display_name }}
      </p>
    </div>

    <!-- Kostenstelle -->
    <div v-else-if="form.assignment_type === 'CostCenter'">
      <label class="label">Kostenstelle *</label>
      <select v-model="form.cost_center_id" class="input w-full" required>
        <option value="">— Auswählen —</option>
        <option v-for="cc in costCenters" :key="cc.id" :value="cc.id">
          {{ cc.cost_center_code }} – {{ cc.name }}
        </option>
      </select>
    </div>

    <div>
      <label class="label">Kommentar</label>
      <textarea v-model="form.comment" class="input w-full" rows="2" />
    </div>

    <div class="flex justify-end gap-2">
      <button type="button" class="btn-secondary" @click="$emit('cancel')">Abbrechen</button>
      <button type="submit" class="btn-primary" :disabled="saving">
        {{ saving ? 'Zuweisen…' : 'Zuweisen' }}
      </button>
    </div>
    <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
  </form>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAssets, useEmployees } from '@/composables/useApi'
import api from '@/composables/useApi'

const props = defineProps({ assetId: { type: String, required: true } })
const emit  = defineEmits(['saved', 'cancel'])

const assetApi    = useAssets()
const employeeApi = useEmployees()

const saving           = ref(false)
const error            = ref('')
const employeeSearch   = ref('')
const employeeResults  = ref([])
const selectedEmployee = ref(null)
const costCenters      = ref([])

const form = reactive({
  assignment_type:       'User',
  employee_id:           '',
  cost_center_id:        '',
  employee_display_name: '',
  employee_mail:         '',
  cost_center_code:      '',
  cost_center_name:      '',
  comment:               '',
})

let searchTimer
function searchEmployees() {
  clearTimeout(searchTimer)
  if (employeeSearch.value.length < 2) { employeeResults.value = []; return }
  searchTimer = setTimeout(async () => {
    const { data } = await employeeApi.list({ search: employeeSearch.value, per_page: 8 })
    employeeResults.value = data.data
  }, 300)
}

function selectEmployee(emp) {
  selectedEmployee.value     = emp
  form.employee_id           = emp.employee_id
  form.employee_display_name = emp.display_name
  form.employee_mail         = emp.mail ?? ''
  employeeSearch.value       = emp.display_name
  employeeResults.value      = []
}

async function submit() {
  if (form.assignment_type === 'User' && !form.employee_id) {
    error.value = 'Bitte einen Mitarbeiter auswählen.'; return
  }
  saving.value = true
  error.value  = ''
  try {
    await assetApi.assign(props.assetId, form)
    emit('saved')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Fehler beim Zuweisen.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const { data } = await api.get('/cost-centers')
  costCenters.value = data.data ?? data
})
</script>
