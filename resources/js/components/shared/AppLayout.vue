<template>
  <div class="flex min-h-screen bg-gray-50">
    <aside class="w-56 flex-shrink-0 border-r border-gray-200 bg-white">
      <div class="flex h-16 items-center border-b border-gray-100 px-6">
        <span class="text-lg font-bold text-indigo-600">ITAMS</span>
      </div>
      <nav class="mt-4 space-y-0.5 px-3">
        <router-link
          v-for="link in navLinks"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50"
          active-class="bg-indigo-50 text-indigo-700 font-medium"
        >
          <span class="text-base">{{ link.icon }}</span>
          {{ link.label }}
        </router-link>
      </nav>
    </aside>

    <div class="flex flex-1 flex-col overflow-hidden">
      <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6">
        <div />
        <div class="flex items-center gap-3 text-sm text-gray-500">
          <span>{{ userName }}</span>
          <button class="btn-ghost text-xs" @click="logout">Abmelden</button>
        </div>
      </header>
      <main class="flex-1 overflow-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/composables/useApi'

const router = useRouter()

const navLinks = [
  { to: '/assets',        icon: '🖥️',  label: 'Assets'      },
  { to: '/categories',    icon: '📂',  label: 'Kategorien'  },
  { to: '/tags',          icon: '🏷️',  label: 'Tags'        },
  { to: '/locations',     icon: '📍',  label: 'Standorte'   },
  { to: '/manufacturers', icon: '🏭',  label: 'Hersteller'  },
]

const userName = computed(() => {
  try {
    const raw = localStorage.getItem('itams_user')
    return raw ? JSON.parse(raw).display_name : ''
  } catch { return '' }
})

async function logout() {
  try { await api.post('/auth/logout') } catch {}
  localStorage.removeItem('itams_token')
  localStorage.removeItem('itams_user')
  router.push('/login')
}
</script>
