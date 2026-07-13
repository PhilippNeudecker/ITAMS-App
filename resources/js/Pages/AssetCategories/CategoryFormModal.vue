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

export type CategoryModalMode = 'create' | 'edit' | 'view' | 'copy';

interface CategoryRecord {
    id: number;
    business_code: string;
    name: string;
    description: string | null;
    color: string | null;
    is_active: boolean;
}

const props = defineProps<{
    open: boolean;
    mode: CategoryModalMode;
    Category?: CategoryRecord | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const titles: Record<CategoryModalMode, string> = {
    create: 'Neue Kategorie',
    edit: 'Kategorie bearbeiten',
    view: 'Kategorie ansehen',
    copy: 'Kategorie kopieren',
};

const descriptions: Record<CategoryModalMode, string> = {
    create: 'Lege einen neue Kategorie an. Neue Kategorien sind standardmäßig aktiv.',
    edit: 'Ändere die Angaben zu dieser Kategorie.',
    view: 'Details zu dieser Kategorie.',
    copy: 'Erstellt eine Kopie dieser Kategorie als neue, aktive Kategorie.',
};

const form = useForm({
    business_code: '',
    name: '',
    description: '' as string | null,
    color: '#2563EB',
    is_active: true,
});

function resetForm() {
    const source = props.Category;

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

watch(() => [props.open, props.mode, props.Category], ([open]) => {
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

    if (props.mode === 'edit' && props.Category) {
        form.patch(route('assets.categorys.update', props.Category.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
        return;
    }

    // create oder copy -> immer ein neuer Category
    form.post(route('assets.categorys.store'), {
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
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ titles[mode] }}</DialogTitle>
                <DialogDescription>{{ descriptions[mode] }}</DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-4 py-2">
                <div class="grid gap-2">
                    <Label for="Category-business-code">Businesscode</Label>
                    <Input id="Category-business-code" v-model="form.business_code" :disabled="isReadOnly" placeholder="z. B. IT" />
                    <p v-if="form.errors.business_code" class="text-sm text-destructive">{{ form.errors.business_code }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="Category-name">Name</Label>
                    <Input id="Category-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name der Kategorie" />
                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="Category-description">Beschreibung</Label>
                    <Textarea id="Category-description" v-model="form.description" :disabled="isReadOnly" placeholder="Optionale Beschreibung" rows="3" />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="Category-color">Farbe</Label>
                    <div class="flex items-center gap-2">
                        <input id="Category-color" type="color" v-model="form.color" :disabled="isReadOnly" class="h-9 w-12 rounded border p-1" />
                        <Input v-model="form.color" :disabled="isReadOnly" class="font-mono" />
                    </div>
                    <p v-if="form.errors.color" class="text-sm text-destructive">{{ form.errors.color }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="Category-color">Übergeordnete Kategorie</Label>
                    <div class="flex items-center gap-2">
                        <input id="parent-category-id" type="number" v-model="form.parent_category_id" :disabled="isReadOnly" class="h-9 w-12 rounded border p-1" />
                        <Input v-model="form.parent_category_id" :disabled="isReadOnly" class="font-mono" />
                    </div>
                    <p v-if="form.errors.parent_category_id" class="text-sm text-destructive">{{ form.errors.parent_category_id }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="Category-prefix">Präfix</Label>
                    <Input id="Category-prefix" v-model="form.asset_prefix" :disabled="isReadOnly" placeholder="Präfix für Assets in dieser Kategorie" />
                    <p v-if="form.errors.asset_prefix" class="text-sm text-destructive">{{ form.errors.asset_prefix }}</p>

                    <Label for="Category-separator">Separator</Label>
                    <div class="flex items-center gap-2">
                        <input id="Category-separator" type="text" v-model="form.separator" :disabled="isReadOnly" class="h-9 w-12 rounded border p-1" />
                        <Input v-model="form.separator" :disabled="isReadOnly" class="font-mono" />
                    </div>
                    <p v-if="form.errors.separator" class="text-sm text-destructive">{{ form.errors.separator }}</p>

                    <Label for="Category-length">Länge</Label>
                    <div class="flex items-center gap-2">
                        <input id="Category-length" type="number" v-model="form.length" :disabled="isReadOnly" class="h-9 w-12 rounded border p-1" />
                        <Input v-model="form.length" :disabled="isReadOnly" class="font-mono" />
                    </div>
                    <p v-if="form.errors.length" class="text-sm text-destructive">{{ form.errors.length }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="Category-warranty-days">Std.-Garantietage</Label>
                    <Input id="Category-warranty-days" v-model="form.default_warranty_days" :disabled="isReadOnly" type="number" />
                    <p v-if="form.errors.default_warranty_days" class="text-sm text-destructive">{{ form.errors.default_warranty_days }}</p>

                    <Label for="Category-warranty-notify-days">Benachrichtigung</Label>
                    <Input id="Category-warranty-notify-days" v-model="form.default_warranty_notify_days_before" :disabled="isReadOnly" type="number" />
                    <p v-if="form.errors.default_warranty_notify_days_before" class="text-sm text-destructive">{{ form.errors.default_warranty_notify_days_before }}</p>
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
