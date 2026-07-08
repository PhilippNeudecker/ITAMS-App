<template>
  <div>
    <div class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50">
      <div class="flex items-center gap-2" :style="{ paddingLeft: `${depth * 1.25}rem` }">
        <span v-if="category.color" class="h-3 w-3 flex-shrink-0 rounded-full" :style="{ backgroundColor: category.color }" />
        <span class="text-sm font-medium text-gray-900">{{ category.name }}</span>
        <span class="font-mono text-xs text-gray-400">{{ category.asset_prefix }}{{ category.asset_separator }}…</span>
      </div>
      <div class="flex gap-2 text-xs">
        <button class="text-indigo-600 hover:underline" @click="$emit('edit', category)">Bearbeiten</button>
        <button class="text-red-400 hover:underline" @click="$emit('delete', category)">Löschen</button>
      </div>
    </div>
    <CategoryTreeNode
      v-for="child in category.children"
      :key="child.id"
      :category="child"
      :depth="depth + 1"
      @edit="$emit('edit', $event)"
      @delete="$emit('delete', $event)"
    />
  </div>
</template>

<script setup>
defineProps({
  category: { type: Object, required: true },
  depth:    { type: Number, default: 0 },
})
defineEmits(['edit', 'delete'])
</script>
