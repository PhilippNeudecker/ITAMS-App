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
    costcenter?: any | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const titles: Record<ModalMode, string> = {
    create: 'Neue Kostenstelle',
    edit: 'Kostenstelle bearbeiten',
    view: 'Kostenstelle ansehen',
    copy: 'Kostenstelle kopieren',
};

const descriptions: Record<ModalMode, string> = {
    create: 'Lege eine neue Kostenstelle an. Neue Kostenstellen sind standardmäßig aktiv.',
    edit: 'Ändere die Angaben zu dieser Kostenstelle.',
    view: 'Details zu dieser Kostenstelle.',
    copy: 'Erstellt eine Kopie dieser Kostenstelle als neue Kostenstelle.',
};

const form = useForm({
    cost_center_code: '',
    name: '',
    description: '',
    is_active: true
});

function resetForm() {
    const source = props.costcenter;

    if ((props.mode === 'edit' || props.mode === 'view') && source) {
        form.cost_center_code = source.module;
        form.name = source.name;
        form.description = source.description;
        form.is_active = source.is_active;
    } else if (props.mode === 'copy' && source) {
        form.cost_center_code = source.code;
        form.name = `${source.name} (Kopie)`;
        form.is_active = true;
    } else {
        form.cost_center_code = '';
        form.name = '';
        form.description = '';
        form.is_active = true;
    }

    form.clearErrors();
}

watch(() => [props.open, props.mode, props.costcenter], ([open]) => {
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

    if (props.mode === 'edit' && props.costcenter) {
        form.patch(route('costcenters.update', props.costcenter.id), {
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

    form.post(route('costcenters.store'), {
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
                        <Label for="costcenter-code">Kostenstelle</Label>
                        <Input id="costcenter-code" v-model="form.cost_center_code" :disabled="isReadOnly" placeholder="Code der Kostenstelle" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.code }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="costcenter-name">Name</Label>
                        <Input id="costcenter-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name der Kostenstelle" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="costcenter-description">Beschreibung</Label>
                        <Input id="costcenter-description" v-model="form.description" :disabled="isReadOnly" type="text" placeholder="Beschreibung" />
                        <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                    </div>
                    <div class="flex items-center justify-between rounded-md border px-3 py-2" :class="(mode === 'create' || mode === 'copy') ? 'hidden' : ''">
                        <div>
                            <Label for="costcenter-active">Aktiv</Label>
                        </div>
                        <Switch id="costcenter-active" :checked="form.is_active" @update:checked="(v: boolean) => form.is_active = v"
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
