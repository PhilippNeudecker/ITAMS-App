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
    locationtypedefinition?: any | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const titles: Record<ModalMode, string> = {
    create: 'Neue Lagerartdefinition',
    edit: 'Lagerartdefinition bearbeiten',
    view: 'Lagerartdefinition ansehen',
    copy: 'Lagerartdefinition kopieren',
};

const descriptions: Record<ModalMode, string> = {
    create: 'Lege eine neue Lagerartdefinition an. Neue Lagerartdefinitionen sind standardmäßig aktiv.',
    edit: 'Ändere die Angaben zu dieser Lagerartdefinition.',
    view: 'Details zu dieser Lagerartdefinition.',
    copy: 'Erstellt eine Kopie dieser Lagerartdefinition als neue Lagerartdefinition.',
};

const form = useForm({
    name: '',
    description: '',
    is_active: true
});

function resetForm() {
    const source = props.locationtypedefinition;

    if ((props.mode === 'edit' || props.mode === 'view') && source) {
        form.name = source.name;
        form.description = source.description;
        form.is_active = source.is_active;
    } else if (props.mode === 'copy' && source) {
        form.name = `${source.name} (Kopie)`;
        form.description = source.description;
        form.is_active = true;
    } else {
        form.name = '';
        form.description = '';
        form.is_active = true;
    }

    form.clearErrors();
}

watch(() => [props.open, props.mode, props.locationtypedefinition], ([open]) => {
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

    if (props.mode === 'edit' && props.locationtypedefinition) {
        form.patch(route('locationtypedefinitions.update', props.locationtypedefinition.id), {
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

    form.post(route('locationtypedefinitions.store'), {
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
                        <Label for="locationtypedefinition-name">Name</Label>
                        <Input id="locationtypedefinition-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name des Status" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="locationtypedefinition-description">Beschreibung</Label>
                        <Input id="locationtypedefinition-description" v-model="form.description" :disabled="isReadOnly" type="text" placeholder="Beschreibung" />
                        <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                    </div>
                    <div class="flex items-center justify-between rounded-md border px-3 py-2" :class="(mode === 'create' || mode === 'copy') ? 'hidden' : ''">
                        <div>
                            <Label for="locationtypedefinition-active">Aktiv</Label>
                        </div>
                        <Switch id="locationtypedefinition-active" :checked="form.is_active" @update:checked="(v: boolean) => form.is_active = v"
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
