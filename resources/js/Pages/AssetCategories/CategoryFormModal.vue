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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export type CategoryModalMode = 'create' | 'edit' | 'view' | 'copy';

// interface CategoryRecord {
//     id: number;
//     business_code: string;
//     name: string;
//     description: string | null;
//     color: string | null;
//     is_active: boolean;
// }

const props = defineProps<{
    open: boolean;
    mode: CategoryModalMode;
    category?: any | null;
    subcategories?: any[];
    initialParentId?: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const isParentLocked = computed(() => props.mode === 'create' && props.initialParentId != null);

const titles: Record<CategoryModalMode, string> = {
    create: 'Neue Kategorie',
    edit: 'Kategorie bearbeiten',
    view: 'Kategorie ansehen',
    copy: 'Kategorie kopieren',
};

const descriptions: Record<CategoryModalMode, string> = {
    create: 'Lege eine neue Kategorie an. Neue Kategorien sind standardmäßig aktiv.',
    edit: 'Ändere die Angaben zu dieser Kategorie.',
    view: 'Details zu dieser Kategorie.',
    copy: 'Erstellt eine Kopie dieser Kategorie als neue, aktive Kategorie.',
};

const form = useForm({
    business_code: '',
    name: '',
    description: '' as string | null,
    color: '#2563EB',
    parent_category_id: null as number | null,
    asset_prefix: '' as string | null,
    asset_separator: '-' as string | null,
    asset_number_length: 6 as number | null,
    default_warranty_days: 365 as number | null,
    default_warranty_notify_days_before: 30 as number | null,
});

function resetForm() {
    const source = props.category;

    if ((props.mode === 'edit' || props.mode === 'view') && source) {
        form.business_code = source.business_code;
        form.name = source.name;
        form.description = source.description ?? '';
        form.color = source.color ?? '#2563EB';
        form.parent_category_id = source.parent_category_id;
        form.asset_prefix = source.asset_prefix ?? '';
        form.asset_separator = source.asset_separator ?? '-';
        form.asset_number_length = source.asset_number_length ?? 6;
        form.default_warranty_days = source.default_warranty_days ?? 365;
        form.default_warranty_notify_days_before = source.default_warranty_notify_days_before ?? 30;
    } else if (props.mode === 'copy' && source) {
        form.business_code = source.business_code;
        form.name = `${source.name} (Kopie)`;
        form.description = source.description ?? '';
        form.color = source.color ?? '#2563EB';
        form.parent_category_id = source.parent_category_id;
        form.asset_prefix = source.asset_prefix ?? '';
        form.asset_separator = source.asset_separator ?? '-';
        form.asset_number_length = source.asset_number_length ?? 6;
        form.default_warranty_days = source.default_warranty_days ?? 365;
        form.default_warranty_notify_days_before = source.default_warranty_notify_days_before ?? 30;
    } else {
        // create
        form.business_code = '';
        form.name = '';
        form.description = '';
        form.color = '#2563EB';
        form.parent_category_id = props.initialParentId ?? null;
        form.asset_prefix = '';
        form.asset_separator = '-';
        form.asset_number_length = 6;
        form.default_warranty_days = 365;
        form.default_warranty_notify_days_before = 30;
    }

    form.clearErrors();
}

watch(() => [props.open, props.mode, props.category, props.initialParentId], ([open]) => {
    if (open) resetForm();
}, { immediate: true });

const parentSelectValue = computed(() =>
    form.parent_category_id == null ? 'none' : String(form.parent_category_id)
);
function onParentSelect(value: string) {
    form.parent_category_id = value === 'none' ? null : Number(value);
}

function close() {
    emit('update:open', false);
}

function submit() {
    if (isReadOnly.value) {
        close();
        return;
    }

    if (props.mode === 'edit' && props.category) {
        form.patch(route('assets.categories.update', props.category.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
        return;
    }

    form.post(route('assets.categories.store'), {
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
                <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                        <Label for="category-business-code">Businesscode</Label>
                        <Input id="category-business-code" v-model="form.business_code" :disabled="isReadOnly" placeholder="z. B. IT" />
                    <p v-if="form.errors.business_code" class="text-sm text-destructive">{{ form.errors.business_code }}</p>
                </div>

                <div class="grid gap-2">
                        <Label for="category-name">Name</Label>
                        <Input id="category-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name der Kategorie" />
                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="category-parent">Elternkategorie</Label>
                    <Select :model-value="parentSelectValue" @update:modelValue="onParentSelect" :disabled="isReadOnly || isParentLocked">
                        <SelectTrigger id="category-parent">
                            <SelectValue placeholder="Hauptkategorie (keine Elternkategorie)" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">— Keine (Hauptkategorie) —</SelectItem>
                            <SelectItem v-for="p in (subcategories ?? [])" :key="p.id" :value="String(p.id)">
                                {{ '\u2007\u2007'.repeat(p.depth) }}{{ p.depth > 0 ? '↳ ' : '' }}{{ p.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        Maximale Tiefe: 3 Ebenen (Hauptkategorie → Kategorie → Subkategorie).
                    </p>
                    <p v-if="form.errors.parent_category_id" class="text-sm text-destructive">{{ form.errors.parent_category_id }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="category-description">Beschreibung</Label>
                    <Textarea id="category-description" v-model="form.description" :disabled="isReadOnly" placeholder="Optionale Beschreibung" rows="3" />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="category-color">Farbe</Label>
                    <div class="flex items-center gap-2">
                        <input id="category-color" type="color" v-model="form.color" :disabled="isReadOnly" class="h-9 w-12 rounded border p-1" />
                        <Input v-model="form.color" :disabled="isReadOnly" class="font-mono" />
                    </div>
                    <p v-if="form.errors.color" class="text-sm text-destructive">{{ form.errors.color }}</p>
                </div>

                <div class="rounded-md border p-3 grid gap-4">
                    <p class="text-sm font-medium">Asset-Nummerierung</p>
                    <div class="grid grid-cols-3 gap-4">
                <div class="grid gap-2">
                            <Label for="category-asset-prefix">Präfix</Label>
                            <Input id="category-asset-prefix" v-model="form.asset_prefix" :disabled="isReadOnly" placeholder="z. B. NB" />
                            <p v-if="form.errors.asset_prefix" class="text-sm text-destructive">{{ form.errors.asset_prefix }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="category-asset-separator">Trennzeichen</Label>
                            <Input id="category-asset-separator" v-model="form.asset_separator" :disabled="isReadOnly" placeholder="-" />
                            <p v-if="form.errors.asset_separator" class="text-sm text-destructive">{{ form.errors.asset_separator }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="category-asset-number-length">Nummernlänge</Label>
                            <Input id="category-asset-number-length" type="number" min="1" max="20"
                                v-model.number="form.asset_number_length" :disabled="isReadOnly" />
                            <p v-if="form.errors.asset_number_length" class="text-sm text-destructive">{{ form.errors.asset_number_length }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border p-3 grid gap-4">
                    <p class="text-sm font-medium">Garantie-Standardwerte</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="category-warranty-days">Garantie (Tage)</Label>
                            <Input id="category-warranty-days" type="number" min="0"
                                v-model.number="form.default_warranty_days" :disabled="isReadOnly" />
                            <p v-if="form.errors.default_warranty_days" class="text-sm text-destructive">{{ form.errors.default_warranty_days }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label for="category-warranty-notify">Hinweis vor Ablauf (Tage)</Label>
                            <Input id="category-warranty-notify" type="number" min="0"
                                v-model.number="form.default_warranty_notify_days_before" :disabled="isReadOnly" />
                            <p v-if="form.errors.default_warranty_notify_days_before" class="text-sm text-destructive">
                                {{ form.errors.default_warranty_notify_days_before }}
                            </p>
                        </div>
                    </div>
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
