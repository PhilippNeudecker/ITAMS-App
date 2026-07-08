import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs:any[]) {
  return twMerge(clsx(inputs));
}

export function formatDate(date:any) {
  if (!date) return "-";
  return new Date(date).toLocaleDateString("de-DE");
}
