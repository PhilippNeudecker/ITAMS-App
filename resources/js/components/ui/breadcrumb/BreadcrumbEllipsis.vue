<script lang="ts" setup>
import type { HTMLAttributes } from "vue"
import { PhDotsThree } from '@phosphor-icons/vue'
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <span
    role="presentation"
    aria-hidden="true"
    :class="cn('flex h-9 w-9 items-center justify-center', props.class)"
  >
    <slot>
      <PhDotsThree class="h-4 w-4" />
    </slot>
    <span class="sr-only">More</span>
  </span>
</template>
