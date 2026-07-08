<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50">
    <div class="w-full max-w-sm space-y-6 rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
      <div class="text-center">
        <h1 class="text-2xl font-bold text-indigo-600">ITAMS</h1>
        <p class="mt-1 text-sm text-gray-500">IT Asset Management System</p>
      </div>

      <form class="space-y-4" @submit.prevent="login">
        <div>
          <label class="label">Benutzername</label>
          <input v-model="form.username" type="text" class="input w-full" autocomplete="username" required />
        </div>
        <div>
          <label class="label">Passwort</label>
          <input v-model="form.password" type="password" class="input w-full" autocomplete="current-password" required />
        </div>
        <button type="submit" class="btn-primary w-full" :disabled="loading">
          {{ loading ? 'Anmelden…' : 'Anmelden' }}
        </button>
        <p v-if="error" class="text-center text-sm text-red-500">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/composables/useApi'

const router  = useRouter()
const loading = ref(false)
const error   = ref('')
const form    = reactive({ username: '', password: '' })

async function login() {
  loading.value = true
  error.value   = ''
  try {
    const { data } = await api.post('/auth/login', form)
    localStorage.setItem('itams_token', data.token)
    localStorage.setItem('itams_user',  JSON.stringify(data.employee))
    router.push('/assets')
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Anmeldung fehlgeschlagen.'
  } finally {
    loading.value = false
  }
}
</script>
