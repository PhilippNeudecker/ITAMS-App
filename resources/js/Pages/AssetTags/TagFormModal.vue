<script setup lang="ts">
import { computed, watch } from 'vue';
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
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';

export type TagModalMode = 'create' | 'edit' | 'view' | 'copy';

const props = defineProps<{
    open: boolean;
    mode: TagModalMode;
    tag?: any | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const titles: Record<TagModalMode, string> = {
    create: 'Neuer Tag',
    edit: 'Tag bearbeiten',
    view: 'Tag ansehen',
    copy: 'Tag kopieren',
};

const descriptions: Record<TagModalMode, string> = {
    create: 'Lege einen neuen Tag an. Neue Tags sind standardmäßig aktiv.',
    edit: 'Ändere die Angaben zu diesem Tag.',
    view: 'Details zu diesem Tag.',
    copy: 'Erstellt eine Kopie dieses Tags als neuen, aktiven Tag.',
};

const form = useForm({
    business_code: '',
    name: '',
    description: '',
    color: '#2563EB',
    is_active: true,
});

function resetForm() {
    const source = props.tag;

    if (props.mode === 'edit' && source) {
        form.business_code = source.business_code;
        form.name = source.name;
        form.description = source.description ?? '';
        form.color = source.color ?? '#2563EB';
        form.is_active = source.is_active;
    } else if (props.mode === 'copy' && source) {
        form.business_code = source.business_code;
        form.name = `${source.name} (Kopie)`;
        form.description = source.description ?? '';
        form.color = source.color ?? '#2563EB';
        form.is_active = true;
    } else if (props.mode === 'view' && source) {
        form.business_code = source.business_code;
        form.name = source.name;
        form.description = source.description ?? '';
        form.color = source.color ?? '#2563EB';
        console.log('// ================ source.is_active = ', source.is_active);
        form.is_active = source.is_active;
    } else {
        form.business_code = '';
        form.name = '';
        form.description = '';
        form.color = '#2563EB';
        form.is_active = true;
    }

    form.clearErrors();
}

watch(() => [props.open, props.mode, props.tag], ([open]) => {
    if (open) resetForm();
}, { immediate: true });

function close() {
    emit('update:open', false);
}

function submit() {
    if (isReadOnly.value) {
        close();
        return;
    }

    if (props.mode === 'edit' && props.tag) {
        form.patch(route('assets.tags.update', props.tag.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
        return;
    }

    // create oder copy -> immer ein neuer Tag
    form.post(route('assets.tags.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('saved');
            close();
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>{{ titles[mode] }}</DialogTitle>
                <DialogDescription>{{ descriptions[mode] }}</DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-4 py-2 max-h-[70vh] overflow-y-auto pr-1">
                <div class="grid gap-2">
                    <Label for="tag-business-code">Businesscode</Label>
                    <Input id="tag-business-code" v-model="form.business_code" :disabled="isReadOnly" placeholder="z. B. IT" />
                    <p v-if="form.errors.business_code" class="text-sm text-destructive">{{ form.errors.business_code }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="tag-name">Name</Label>
                    <Input id="tag-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name des Tags" />
                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="tag-description">Beschreibung</Label>
                    <Textarea id="tag-description" v-model="form.description" :disabled="isReadOnly" placeholder="Optionale Beschreibung" rows="3" />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="tag-color">Farbe</Label>
                    <div class="flex items-center gap-2">
                        <input id="tag-color" type="color" v-model="form.color" :disabled="isReadOnly" class="h-9 w-12 rounded border p-1" />
                        <Input v-model="form.color" :disabled="isReadOnly" class="font-mono" />
                    </div>
                    <p v-if="form.errors.color" class="text-sm text-destructive">{{ form.errors.color }}</p>
                </div>

                <div class="flex items-center justify-between rounded-md border px-3 py-2" :class="(mode === 'create' || mode === 'copy') ? 'hidden' : ''">
                    <div>
                        <Label for="tag-active">Aktiv</Label>
                    </div>
                    <Switch id="tag-active" :model-value="form.is_active" @update:checked="(v: boolean) => form.is_active = v"
                        :disabled="isReadOnly || mode === 'create' || mode === 'copy'" />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" type="button" @click="close">
                    {{ isReadOnly ? 'Schließen' : 'Abbrechen' }}
                </Button>
                <Button v-if="!isReadOnly" type="button" :disabled="form.processing" @click="submit">
                    Speichern
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
