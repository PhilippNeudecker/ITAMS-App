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

const processing = ref(false);
const props = defineProps<{
    open: boolean;
    mode: ModalMode;
    manufacturer?: any | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const titles: Record<ModalMode, string> = {
    create: 'Neuer Hersteller',
    edit: 'Hersteller bearbeiten',
    view: 'Hersteller ansehen',
    copy: 'Hersteller kopieren',
};

const descriptions: Record<ModalMode, string> = {
    create: 'Lege einen neuen Hersteller an. Neue Hersteller sind standardmäßig aktiv.',
    edit: 'Ändere die Angaben zu diesem Hersteller.',
    view: 'Details zu diesem Hersteller.',
    copy: 'Erstellt eine Kopie dieses Herstellers als neuer Hersteller.',
};

const form = useForm({
    // business_code: '',
    name: '',
    website: '' as string | '',
    support_contact: '' as string | '',
});

function resetForm() {
    const source = props.manufacturer;

    if ((props.mode === 'edit' || props.mode === 'view') && source) {
        // form.business_code = source.business_code;
        form.name = source.name;
        form.website = source.website;
        form.support_contact = source.support_contact;
    } else if (props.mode === 'copy' && source) {
        // form.business_code = source.business_code;
        form.name = `${source.name} (Kopie)`;
        form.website = source.website;
        form.support_contact = source.support_contact;
    } else {
        // form.business_code = source.business_code;
        form.name = '';
        form.website = '';
        form.support_contact = '';
    }

    form.clearErrors();
}

watch(() => [props.open, props.mode, props.manufacturer], ([open]) => {
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

    if (props.mode === 'edit' && props.manufacturer) {
        form.patch(route('assets.manufacturers.update', props.manufacturer.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
        return;
    }

    form.post(route('assets.manufacturers.store'), {
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
                    <!-- <div class="grid gap-2">
                        <Label for="category-business-code">Businesscode</Label>
                        <Input id="category-business-code" v-model="form.business_code" :disabled="isReadOnly" placeholder="z. B. IT" />
                    <p v-if="form.errors.business_code" class="text-sm text-destructive">{{ form.errors.business_code }}</p>
                </div> -->

                    <div class="grid gap-2">
                        <Label for="manufacturer-name">Name</Label>
                        <Input id="manufacturer-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name des Herstellers" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="website">Webseite</Label>
                        <Input id="website" v-model="form.website" :disabled="isReadOnly" type="url" placeholder="Website" />
                        <p v-if="form.errors.website" class="text-sm text-destructive">{{ form.errors.website }}</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="support_contact">Support Kontakt</Label>
                        <Input id="support_contact" v-model="form.support_contact" type="email" :disabled="isReadOnly" placeholder="Kontakt@MailAdresse.com" />
                        <p v-if="form.errors.support_contact" class="text-sm text-destructive">{{ form.errors.support_contact }}</p>
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
