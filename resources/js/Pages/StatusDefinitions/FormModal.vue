<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2 } from 'lucide-vue-next';
import { type ModalMode } from '@/interfaces/ModalMode';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';

const processing = ref(false);
const props = defineProps<{
    open: boolean;
    mode: ModalMode;
    statusdefinition?: any | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const titles: Record<ModalMode, string> = {
    create: 'Neue Statusdefinition',
    edit: 'Statusdefinition bearbeiten',
    view: 'Statusdefinition ansehen',
    copy: 'Statusdefinition kopieren',
};

const descriptions: Record<ModalMode, string> = {
    create: 'Lege eine neue Statusdefinition an. Neue Statusdefinitionen sind standardmäßig aktiv.',
    edit: 'Ändere die Angaben zu dieser Statusdefinition.',
    view: 'Details zu dieser Statusdefinition.',
    copy: 'Erstellt eine Kopie dieser Statusdefinition als neue Statusdefinition.',
};

const form = useForm({
    module: '',
    name: '',
    description: '',
    sort_order: 0,
    color: '#256ABC',
    is_active: true
});

function resetForm() {
    const source = props.statusdefinition;

    if ((props.mode === 'edit' || props.mode === 'view') && source) {
        form.module = source.module;
        form.name = source.name;
        form.description = source.description;
        form.sort_order = source.sort_order
        form.color = source.color;
        form.is_active = source.is_active;
    } else if (props.mode === 'copy' && source) {
        form.module = source.module;
        form.name = `${source.name} (Kopie)`;
        form.description = source.description;
        form.sort_order = source.sort_order
        form.color = source.color;
        form.is_active = true;
    } else {
        form.module = '';
        form.name = '';
        form.description = '';
        form.sort_order = 1;
        form.color = '#256ABC';
        form.is_active = true;
    }

    form.clearErrors();
}

watch(() => [props.open, props.mode, props.statusdefinition], ([open]) => {
    if (open) resetForm();
}, { immediate: true });

function close() {
    emit('update:open', false);
}

function onSubmit() {
    if (isReadOnly.value) {
        close();
        return;
    }
    processing.value = true;

    if (props.mode === 'edit' && props.statusdefinition) {
        form.patch(route('statusdefinitions.update', props.statusdefinition.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
        processing.value = false;
        return;
    }

    form.post(route('statusdefinitions.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('saved');
            close();
        },
    });
    processing.value = false;
}
</script>

<template>
    <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
        <DialogContent class="sm:max-w-xl">
            <form @submit.prevent="onSubmit">
                <DialogHeader>
                    <DialogTitle>{{ titles[mode] }}</DialogTitle>
                    <DialogDescription>{{ descriptions[mode] }}</DialogDescription>
                </DialogHeader>

                <div class="flex flex-col gap-4 py-2 max-h-[70vh] overflow-y-auto pr-1">
                    <div class="grid gap-2">
                        <Label for="statusdefinition-module">Modul</Label>
                        <Select v-model="form.module">
                            <SelectTrigger>
                                <SelectValue placeholder="Modul wählen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Asset">Asset</SelectItem>
                                <SelectItem value="Contract">Vertrag</SelectItem>
                                <SelectItem value="Machine">Maschine</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.module }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="statusdefinition-name">Name</Label>
                        <Input id="statusdefinition-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name des Status" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="statusdefinition-description">Beschreibung</Label>
                        <Input id="statusdefinition-description" v-model="form.description" :disabled="isReadOnly" type="text" placeholder="Beschreibung" />
                        <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="statusdefinition-sort-order">Sortierung</Label>
                        <Input id="statusdefinition-sort-order" type="number" min="1" max="9999" v-model="form.sort_order" :disabled="isReadOnly" placeholder="Sortierung" />
                        <p v-if="form.errors.sort_order" class="text-sm text-destructive">{{ form.errors.sort_order }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="statusdefinition-color">Farbe</Label>
                        <div class="flex items-center gap-2">
                            <input id="statusdefinition-color" type="color" v-model="form.color" :disabled="isReadOnly" class="h-9 w-12 rounded border p-1" />
                            <Input v-model="form.color" :disabled="isReadOnly" class="font-mono" />
                        </div>
                        <p v-if="form.errors.color" class="text-sm text-destructive">{{ form.errors.color }}</p>
                    </div>

                    <div class="flex items-center justify-between rounded-md border px-3 py-2" :class="(mode === 'create' || mode === 'copy') ? 'hidden' : ''">
                        <div>
                            <Label for="statusdefinition-active">Aktiv</Label>
                        </div>
                        <Switch id="statusdefinition-active" :checked="form.is_active" @update:checked="(v: boolean) => form.is_active = v"
                            :disabled="isReadOnly || mode === 'create' || mode === 'copy'" />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" type="button" @click="close">
                        {{ isReadOnly ? 'Schließen' : 'Abbrechen' }}
                    </Button>
                    <Button v-if="!isReadOnly" :disabled="form.processing" type="submit">
                        <Loader2 v-if="processing" class="w-4 h-4 mr-2 animate-spin" />
                        Speichern
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
