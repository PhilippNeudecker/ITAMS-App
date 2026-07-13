import type { Updater } from '@tanstack/vue-table'
import type { ClassValue } from 'clsx'

import type { Ref } from 'vue'
import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs:any[]) {
  return twMerge(clsx(inputs));
}

export function formatDate(date:any) {
  if (!date) return "-";
  return new Date(date).toLocaleDateString("de-DE");
}
