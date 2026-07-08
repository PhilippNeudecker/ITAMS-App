<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Kategorien</h1>
      <button class="btn-primary" @click="openCreate">+ Neue Kategorie</button>
    </div>

    <div class="card">
      <CategoryTreeNode
        v-for="cat in rootCategories"
        :key="cat.id"
        :category="cat"
        @edit="openEdit"
        @delete="deleteCategory"
      />
      <p v-if="rootCategories.length === 0" class="py-8 text-center text-sm text-gray-400">
        Noch keine Kategorien vorhanden.
      </p>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
          <h2 class="mb-4 text-lg font-semibold">{{ editing ? 'Kategorie bearbeiten' : 'Neue Kategorie' }}</h2>
          <CategoryForm
            :initial="editing"
            :categories="allCategories"
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
import { useCategories } from '@/composables/useApi'
import CategoryTreeNode from './CategoryTreeNode.vue'
import CategoryForm from './CategoryForm.vue'

const api           = useCategories()
const allCategories = ref([])
const showModal     = ref(false)
const editing       = ref(null)

const rootCategories = computed(() =>
  allCategories.value
    .filter(c => !c.parent_category_id)
    .map(c => ({ ...c, children: childrenOf(c.id) }))
)

function childrenOf(parentId) {
  return allCategories.value
    .filter(c => c.parent_category_id === parentId)
    .map(c => ({ ...c, children: childrenOf(c.id) }))
}

async function load() {
  const { data } = await api.list()
  allCategories.value = data
}

function openCreate() { editing.value = null; showModal.value = true }
function openEdit(cat) { editing.value = cat; showModal.value = true }

async function deleteCategory(cat) {
  if (!confirm(`Kategorie "${cat.name}" wirklich löschen?`)) return
  await api.remove(cat.id)
  await load()
}

async function onSaved() {
  showModal.value = false
  await load()
}

onMounted(load)
</script>
