<script setup lang="ts">
import { MoreHorizontal, FileSearchIcon, PenIcon, CopyIcon, Trash2Icon, FolderPlusIcon } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const props = defineProps<{
    data: {
        id: string;
    };
}>();

const emit = defineEmits<{
    (e: 'view', id: number): void;
    (e: 'edit', id: number): void;
    (e: 'copy', id: number): void;
    (e: 'addChild', id: number): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0" @click.stop>
                <span class="sr-only">Aktionen öffnen</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" @click.stop>
            <DropdownMenuLabel>Aktionen</DropdownMenuLabel>
            <DropdownMenuItem @click="emit('view', data.id)">
                <FileSearchIcon class="w-4 h-4" />
                Anzeigen
            </DropdownMenuItem>
            <DropdownMenuItem @click="emit('edit', data.id)">
                <PenIcon class="w-4 h-4" />
                Bearbeiten
            </DropdownMenuItem>
            <DropdownMenuItem @click="emit('copy', data.id)">
                <CopyIcon class="w-4 h-4" />
                Kopieren
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem class="text-destructive" @click="emit('delete', data.id)">
                <Trash2Icon class="w-4 h-4" />
                Löschen
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
