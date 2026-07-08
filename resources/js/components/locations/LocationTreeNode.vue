<template>
  <div>
    <div
      class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50"
      :style="{ paddingLeft: `${(depth * 1.25) + 0.75}rem` }"
    >
      <div class="flex items-center gap-2 text-sm">
        <span class="text-xs text-gray-400">{{ location.locationType?.name }}</span>
        <span class="font-medium text-gray-900">{{ location.name }}</span>
        <span v-if="location.city" class="text-xs text-gray-400">· {{ location.city }}</span>
      </div>
      <div class="flex gap-2 text-xs">
        <button class="text-indigo-600 hover:underline" @click="$emit('edit', location)">Bearbeiten</button>
        <button class="text-red-400 hover:underline" @click="$emit('delete', location)">Löschen</button>
      </div>
    </div>
    <LocationTreeNode
      v-for="child in location.children"
      :key="child.id"
      :location="child"
      :depth="depth + 1"
      @edit="$emit('edit', $event)"
      @delete="$emit('delete', $event)"
    />
  </div>
</template>

<script setup>
defineProps({
  location: { type: Object, required: true },
  depth:    { type: Number, default: 0 },
})
defineEmits(['edit', 'delete'])
</script>
